# Arquitectura Propuesta — Motor de Facturación Electrónica Multiempresa

Fecha: 2026-10-27
Proyecto: ROAVAL
Decisión: Motor propio (Opción A)

---

## 1. Principios de Diseño

1. **Modularidad**: Cada responsabilidad en su propio servicio (SRP). Interfaz `FacturadorInterface` para abstraer el mecanismo de emisión.
2. **Multi-tenancy nativo**: Todo scoped por `id_concession`. Cada concesión tiene su RUT, certificado, folios y configuración independiente.
3. **Seguridad desde el diseño**: Contraseñas cifradas, certificados fuera del web root, locking de folios, auditoría completa.
4. **Compatibilidad**: PHP 8.2.26, Laravel 8.83.27, sin dependencias AGPL.
5. **Extensibilidad**: Boleta 39 primero, arquitectura preparada para 41, 33, 34, 61 sin refactoring.
6. **Idempotencia**: Cada emisión tiene clave única para evitar duplicados.
7. **Resiliencia**: Retry con backoff, timeouts explícitos, estados de error recuperables.

---

## 2. Estructura de Directorios

```
app/
├── Contracts/
│   └── FacturadorInterface.php           # Contrato principal del facturador
│
├── Services/Dte/
│   ├── DteService.php                     # Orquestador principal (implementa FacturadorInterface)
│   │
│   ├── Xml/
│   │   ├── DteXmlBuilder.php              # Construcción XML con DOMDocument
│   │   ├── XmlSigner.php                  # Firma XMLDSIG enveloped
│   │   └── TedGenerator.php               # Timbre electrónico (TED + FRMT)
│   │
│   ├── Tax/
│   │   └── TributaryCalculator.php        # IVA, neto, exento, totales
│   │
│   ├── Folio/
│   │   ├── CafManager.php                 # Carga, parsing y validación de CAF
│   │   └── FolioAllocator.php             # Asignación atómica con SELECT...FOR UPDATE
│   │
│   ├── Sii/
│   │   ├── SiiAuthClient.php              # getSemilla → getToken (SOAP)
│   │   ├── SiiBoletaClient.php            # Envío de boletas al SII
│   │   ├── SiiDteClient.php               # Envío de DTE al SII (fase futura)
│   │   ├── SiiStatusClient.php            # Consulta de estado (SOAP QueryEstUp)
│   │   └── SiiResponseParser.php          # Parsing de respuestas XML del SII
│   │
│   ├── Sync/
│   │   └── DteStatusSynchronizer.php      # Polling de estados pendientes
│   │
│   └── Pdf/
│       └── DtePdfGenerator.php            # PDF con código de barras TED (PDF417)
│
├── Http/Controllers/Dte/
│   ├── BoletaController.php               # CRUD + emisión de boletas
│   ├── CertificadoController.php          # Upload y gestión de certificados .p12
│   ├── CafController.php                  # Upload y gestión de CAF
│   ├── ConfiguracionFiscalController.php  # Datos fiscales por concesión
│   └── DteConsultaController.php          # Consulta de estados al SII
│
├── Models/Dte/
│   ├── ConfiguracionFiscal.php
│   ├── Certificado.php
│   ├── CafFolio.php
│   ├── DocumentoTributario.php
│   ├── DocumentoTributarioDetalle.php
│   ├── EnvioSii.php
│   └── AuditDte.php
│
├── Jobs/Dte/
│   ├── SendBoletaToSiiJob.php             # Envío asíncrono de boleta
│   └── SyncDteStatusJob.php               # Polling de estados pendientes
│
└── DTOs/
    ├── BoletaData.php                     # Value object para datos de boleta
    └── CaratulaData.php                   # Value object para datos de carátula
```

---

## 3. Interfaz Principal

```php
<?php

namespace App\Contracts;

interface FacturadorInterface
{
    /**
     * Emitir un documento tributario electrónico.
     *
     * @param int $concessionId  ID de la concesión emisora
     * @param int $tipoDocumento Código SII (39, 41, 33, 34, 61)
     * @param array $datos       Datos del documento (receptor, detalle, etc.)
     * @return DocumentoTributario
     * @throws DteException
     */
    public function emitir(int $concessionId, int $tipoDocumento, array $datos): DocumentoTributario;

    /**
     * Anular un documento previamente emitido.
     * Para boletas: emitir nota de crédito que referencia la boleta original.
     */
    public function anular(DocumentoTributario $documento, string $motivo): DocumentoTributario;

    /**
     * Consultar estado de un documento en el SII.
     */
    public function consultarEstado(DocumentoTributario $documento): string;
}
```

---

## 4. Esquema de Base de Datos

### 4.1 `configuracion_fiscal`

Datos fiscales de cada concesión para emisión de DTE.

```
id                  BIGINT UNSIGNED PK AUTO_INCREMENT
id_concession       BIGINT UNSIGNED FK(concessions.id) UNIQUE NOT NULL
rut_empresa         VARCHAR(12) NOT NULL          -- "76123456-K"
razon_social        VARCHAR(100) NOT NULL
giro                VARCHAR(80) NOT NULL          -- Max 80 chars SII
actividad_economica INT UNSIGNED NOT NULL         -- Código CIIU
direccion           VARCHAR(70) NOT NULL          -- Max 70 chars SII
comuna              VARCHAR(20) NOT NULL          -- Código o nombre comuna
ciudad              VARCHAR(20) NOT NULL
telefono            VARCHAR(20) NULLABLE
email_dte           VARCHAR(100) NULLABLE         -- Email para intercambio DTE
sucursal_sii        INT UNSIGNED DEFAULT 0        -- 0 = casa matriz
fecha_resolucion    DATE NOT NULL                 -- FchResol
numero_resolucion   INT NOT NULL                  -- NroResol (0=certificación)
ambiente            ENUM('certificacion','produccion') DEFAULT 'certificacion'
activa              BOOLEAN DEFAULT false         -- Habilitar emisión
created_at          TIMESTAMP
updated_at          TIMESTAMP
```

**Relación**: `concessions.id` 1:1 `configuracion_fiscal.id_concession`

### 4.2 `dte_certificados`

Certificados digitales (.p12) por concesión.

```
id                  BIGINT UNSIGNED PK AUTO_INCREMENT
id_concession       BIGINT UNSIGNED FK(concessions.id) NOT NULL
rut_titular         VARCHAR(12) NOT NULL          -- RUT del titular del certificado
nombre_titular      VARCHAR(100) NOT NULL
archivo_path        VARCHAR(255) NOT NULL         -- Ruta relativa en storage
password_cifrada    TEXT NOT NULL                  -- Crypt::encryptString()
fecha_vencimiento   DATE NOT NULL
activo              BOOLEAN DEFAULT false
created_at          TIMESTAMP
updated_at          TIMESTAMP

INDEX: (id_concession, activo)
```

**Almacenamiento de archivos**: `storage/app/dte/certificados/{id_concession}/{uuid}.p12`
- Fuera del directorio web público
- Nombre UUID para evitar path traversal
- Permisos 0640

### 4.3 `caf_folios`

Rangos de folios autorizados (CAF) por tipo de documento y concesión.

```
id                  BIGINT UNSIGNED PK AUTO_INCREMENT
id_concession       BIGINT UNSIGNED FK(concessions.id) NOT NULL
tipo_documento      SMALLINT UNSIGNED NOT NULL     -- 39, 41, 33, 34, 61
folio_desde         INT UNSIGNED NOT NULL
folio_hasta         INT UNSIGNED NOT NULL
folio_actual        INT UNSIGNED NOT NULL          -- Próximo folio a asignar
xml_cifrado         TEXT NOT NULL                  -- Crypt::encryptString(XML)
fecha_autorizacion  DATE NOT NULL                  -- FA del CAF
fecha_vencimiento   DATE NULLABLE                  -- FA + 180 días (tipos 33,43,46,56,61)
idk                 SMALLINT UNSIGNED NOT NULL     -- 100=cert, 300=prod
agotado             BOOLEAN DEFAULT false
created_at          TIMESTAMP
updated_at          TIMESTAMP

UNIQUE: (id_concession, tipo_documento, folio_desde, folio_hasta)
INDEX: (id_concession, tipo_documento, agotado)
```

### 4.4 `documentos_tributarios`

Documento DTE completo con su ciclo de vida.

```
id                  BIGINT UNSIGNED PK AUTO_INCREMENT
id_concession       BIGINT UNSIGNED FK(concessions.id) NOT NULL
tipo_documento      SMALLINT UNSIGNED NOT NULL     -- 39, 41, 33, 34, 61
folio               INT UNSIGNED NOT NULL
fecha_emision       DATE NOT NULL
rut_emisor          VARCHAR(12) NOT NULL
rut_receptor        VARCHAR(12) NOT NULL           -- "66666666-6" para anónimo
razon_social_receptor VARCHAR(100) NOT NULL
monto_neto          INT DEFAULT 0
monto_iva           INT DEFAULT 0
tasa_iva            DECIMAL(4,2) DEFAULT 19.00
monto_exento        INT DEFAULT 0
monto_total         INT NOT NULL
xml_documento       LONGTEXT NULLABLE              -- XML del DTE firmado
xml_sobre           LONGTEXT NULLABLE              -- XML del EnvioBOLETA/EnvioDTE firmado
ted_xml             TEXT NULLABLE                   -- XML del TED (para PDF417)
estado              ENUM('borrador','emitido','enviado','aceptado','rechazado','anulado')
                    DEFAULT 'borrador'
track_id            VARCHAR(50) NULLABLE           -- TrackID del SII
respuesta_sii       TEXT NULLABLE                  -- Última respuesta raw del SII
envio_intentos      SMALLINT UNSIGNED DEFAULT 0
ultimo_envio_at     TIMESTAMP NULLABLE
orden_servicio_id   BIGINT UNSIGNED FK(ordenes_servicio.id) NULLABLE
idempotency_key     VARCHAR(64) UNIQUE NOT NULL    -- sha256(concession:tipo:orden:ts)
caf_folio_id        BIGINT UNSIGNED FK(caf_folios.id) NOT NULL
created_at          TIMESTAMP
updated_at          TIMESTAMP
deleted_at          TIMESTAMP NULLABLE              -- SoftDeletes

UNIQUE: (id_concession, tipo_documento, folio)
INDEX: (id_concession, estado)
INDEX: (id_concession, fecha_emision)
INDEX: (orden_servicio_id)
```

### 4.5 `documento_tributario_detalles`

Líneas de detalle del documento tributario.

```
id                  BIGINT UNSIGNED PK AUTO_INCREMENT
documento_id        BIGINT UNSIGNED FK(documentos_tributarios.id) CASCADE NOT NULL
nro_linea           SMALLINT UNSIGNED NOT NULL     -- NroLinDet (1-based)
nombre_item         VARCHAR(80) NOT NULL           -- NmbItem (max 80 chars SII)
descripcion         VARCHAR(1000) NULLABLE         -- DscItem
cantidad            DECIMAL(12,6) NOT NULL DEFAULT 1
unidad_medida       VARCHAR(4) NULLABLE            -- UnmdItem
precio_unitario     DECIMAL(12,2) NOT NULL         -- PrcItem
descuento_pct       DECIMAL(5,2) DEFAULT 0         -- DescuentoPct
descuento_monto     INT DEFAULT 0                  -- DescuentoMonto
monto_item          INT NOT NULL                   -- MontoItem
producto_id         BIGINT UNSIGNED FK(products.id) NULLABLE
servicio_id         BIGINT UNSIGNED FK(servicios.id) NULLABLE
created_at          TIMESTAMP
updated_at          TIMESTAMP

INDEX: (documento_id)
```

### 4.6 `envios_sii`

Log de cada intento de envío al SII.

```
id                  BIGINT UNSIGNED PK AUTO_INCREMENT
id_concession       BIGINT UNSIGNED FK(concessions.id) NOT NULL
tipo_envio          ENUM('boleta','dte','rcof') NOT NULL
documento_id        BIGINT UNSIGNED FK(documentos_tributarios.id) NULLABLE
xml_enviado_hash    VARCHAR(64) NOT NULL           -- sha256 del XML enviado
respuesta_raw       TEXT NULLABLE
track_id            VARCHAR(50) NULLABLE
status_code         SMALLINT NULLABLE              -- STATUS del SII (0-99)
http_status         SMALLINT NULLABLE              -- HTTP status code
intentos            SMALLINT UNSIGNED DEFAULT 1
ambiente            ENUM('certificacion','produccion') NOT NULL
created_at          TIMESTAMP
updated_at          TIMESTAMP

INDEX: (id_concession, tipo_envio, created_at)
INDEX: (track_id)
```

### 4.7 `audit_dte`

Auditoría granular de cada acción en el proceso DTE.

```
id                  BIGINT UNSIGNED PK AUTO_INCREMENT
id_concession       BIGINT UNSIGNED FK(concessions.id) NOT NULL
documento_id        BIGINT UNSIGNED FK(documentos_tributarios.id) NULLABLE
accion              VARCHAR(50) NOT NULL
                    -- 'folio_asignado', 'xml_generado', 'ted_firmado',
                    -- 'sobre_firmado', 'enviado_sii', 'estado_cambiado',
                    -- 'estado_consultado', 'pdf_generado', 'anulado'
detalle             JSON NULLABLE                  -- Datos adicionales por acción
estado_anterior     VARCHAR(20) NULLABLE
estado_nuevo        VARCHAR(20) NULLABLE
user_id             BIGINT UNSIGNED FK(users.id) NULLABLE
ip_address          VARCHAR(45) NULLABLE
created_at          TIMESTAMP

INDEX: (id_concession, documento_id)
INDEX: (created_at)
```

### 4.8 Diagrama de Relaciones

```
concessions (existente)
    │
    ├── 1:1 ── configuracion_fiscal
    ├── 1:N ── dte_certificados
    ├── 1:N ── caf_folios
    ├── 1:N ── documentos_tributarios
    │              ├── 1:N ── documento_tributario_detalles
    │              ├── 1:N ── envios_sii
    │              └── 1:N ── audit_dte
    └── 1:N ── envios_sii (directamente, para RCOF sin documento)

ordenes_servicio (existente)
    └── 1:N ── documentos_tributarios (una orden puede tener boleta + nota crédito)
```

---

## 5. Servicios — Diseño Detallado

### 5.1 DteService (Orquestador)

```php
class DteService implements FacturadorInterface
{
    public function __construct(
        private TributaryCalculator $calculator,
        private FolioAllocator $folioAllocator,
        private DteXmlBuilder $xmlBuilder,
        private TedGenerator $tedGenerator,
        private XmlSigner $xmlSigner,
        private SiiAuthClient $authClient,
        private SiiBoletaClient $boletaClient,
        private DtePdfGenerator $pdfGenerator
    ) {}

    public function emitir(int $concessionId, int $tipoDocumento, array $datos): DocumentoTributario
    {
        // 1. Validar configuración fiscal activa
        // 2. Validar certificado vigente
        // 3. Calcular montos tributarios
        // 4. Asignar folio (atómico, dentro de transacción)
        // 5. Construir XML del documento
        // 6. Generar TED y firmarlo con CAF RSASK
        // 7. Firmar documento con certificado digital
        // 8. Construir envelope (EnvioBOLETA o EnvioDTE)
        // 9. Firmar envelope
        // 10. Guardar en BD (estado='emitido')
        // 11. Dispatch job para enviar al SII
        // 12. Retornar DocumentoTributario
    }
}
```

### 5.2 TributaryCalculator

```php
class TributaryCalculator
{
    private const TASA_IVA = 19;

    // Boletas: precios incluyen IVA
    public function calcularDesdeBruto(int $montoTotal): array
    {
        $neto = (int) round($montoTotal / (1 + self::TASA_IVA / 100));
        $iva = $montoTotal - $neto;
        return ['neto' => $neto, 'iva' => $iva, 'total' => $montoTotal];
    }

    // Facturas: precios son netos
    public function calcularDesdeNeto(int $montoNeto): array
    {
        $iva = (int) round($montoNeto * self::TASA_IVA / 100);
        return ['neto' => $montoNeto, 'iva' => $iva, 'total' => $montoNeto + $iva];
    }
}
```

### 5.3 FolioAllocator

```php
class FolioAllocator
{
    public function asignar(int $concessionId, int $tipoDocumento): array
    {
        return DB::transaction(function () use ($concessionId, $tipoDocumento) {
            $caf = CafFolio::where('id_concession', $concessionId)
                ->where('tipo_documento', $tipoDocumento)
                ->where('agotado', false)
                ->lockForUpdate()    // <-- Previene race condition
                ->orderBy('folio_desde')
                ->firstOrFail();

            $folio = $caf->folio_actual;

            if ($folio > $caf->folio_hasta) {
                $caf->update(['agotado' => true]);
                throw new FoliosAgotadosException("Sin folios para tipo $tipoDocumento");
            }

            $caf->increment('folio_actual');

            return ['folio' => $folio, 'caf' => $caf];
        });
    }
}
```

### 5.4 TedGenerator

```php
class TedGenerator
{
    public function generar(array $datos, string $cafXml): string
    {
        // 1. Construir <DD> con DOMDocument
        //    RE, TD, F, FE, RR, RSR(max40), MNT, IT1(max40), CAF, TSTED
        //
        // 2. Canonicalizar DD
        //    $dd = $ddNode->C14N();
        //
        // 3. Convertir a ISO-8859-1
        //    $dd = mb_convert_encoding($dd, 'ISO-8859-1', 'UTF-8');
        //
        // 4. Colapsar whitespace entre tags
        //    $dd = preg_replace('/>\s+</', '><', $dd);
        //
        // 5. Firmar con clave privada del CAF
        //    openssl_sign($dd, $firma, $rsask, OPENSSL_ALGO_SHA1);
        //
        // 6. Retornar TED XML completo con FRMT
    }
}
```

### 5.5 XmlSigner

```php
class XmlSigner
{
    public function firmar(DOMDocument $doc, string $referenceId, $privateKey, string $x509Cert): void
    {
        // 1. Buscar elemento por ID: $doc->getElementById($referenceId)
        //
        // 2. DigestValue = base64_encode(sha1($element->C14N(), true))
        //
        // 3. Construir <SignedInfo> con:
        //    CanonicalizationMethod: C14N
        //    SignatureMethod: rsa-sha1
        //    Reference URI="#{$referenceId}":
        //      Transform: enveloped-signature
        //      DigestMethod: sha1
        //      DigestValue
        //
        // 4. SignatureValue = base64_encode(openssl_sign(
        //        $signedInfoNode->C14N(), $sig, $privateKey, OPENSSL_ALGO_SHA1
        //    ))
        //
        // 5. KeyInfo con RSAKeyValue (Modulus, Exponent) y X509Certificate
        //
        // 6. Insertar <Signature> en el documento
    }
}
```

### 5.6 SiiAuthClient

```php
class SiiAuthClient
{
    public function obtenerToken(int $concessionId): string
    {
        $cacheKey = "sii_token_{$concessionId}";

        return Cache::remember($cacheKey, 3600, function () use ($concessionId) {
            // 1. SOAP CrSeed.jws → getSeed() → obtener semilla
            // 2. Construir XML: <getToken><item><Semilla>...</Semilla></item></getToken>
            // 3. Firmar XML con certificado de la concesión
            // 4. SOAP GetTokenFromSeed.jws → getToken(pszXml)
            // 5. Parsear respuesta → extraer TOKEN cuando ESTADO='00'
            // 6. Retornar token string
        });
    }

    private function getWsdlUrl(string $service, string $ambiente): string
    {
        $server = $ambiente === 'produccion' ? 'palena' : 'maullin';
        return "https://{$server}.sii.cl/DTEWS/{$service}.jws?WSDL";
    }
}
```

---

## 6. Flujo Completo — Emisión de Boleta 39

```
Usuario → "Emitir Boleta" en OrdenServicio
   │
   ▼
BoletaController::store()
   │
   ├── Validar permisos: auth()->user()->can('dte.boletas.emitir')
   ├── Validar id_concession == auth()->user()->id_concession
   │
   ▼
DteService::emitir(concessionId, 39, datos)
   │
   ├── 1. Cargar ConfiguracionFiscal (validar activa)
   ├── 2. Cargar Certificado (validar vigente, descifrar password)
   │
   ├── 3. TributaryCalculator::calcularDesdeBruto(montoTotal)
   │      → {neto: 8403, iva: 1597, total: 10000}
   │
   ├── 4. FolioAllocator::asignar(concessionId, 39)
   │      → DB::transaction + lockForUpdate
   │      → {folio: 123, caf: CafFolio}
   │
   ├── 5. DteXmlBuilder::construirBoleta39(datos, folio, config)
   │      → DOMDocument con Encabezado, Detalle
   │      → encoding="ISO-8859-1"
   │
   ├── 6. TedGenerator::generar(ddData, cafXml)
   │      → DD + C14N + ISO-8859-1 + SHA1withRSA(RSASK)
   │      → Insertar <TED> en documento
   │
   ├── 7. XmlSigner::firmar(doc, "DTE_T39F123", privateKey, x509)
   │      → XMLDSIG enveloped sobre <Documento>
   │
   ├── 8. DteXmlBuilder::construirEnvioBoleta(documentos, config)
   │      → <EnvioBOLETA> con <SetDTE>, <Caratula>
   │
   ├── 9. XmlSigner::firmar(envelope, "DTE_SetDoc", privateKey, x509)
   │      → XMLDSIG sobre <SetDTE>
   │
   ├── 10. Guardar DocumentoTributario (estado='emitido')
   │       + DocumentoTributarioDetalle (líneas)
   │       + AuditDte (acciones: folio_asignado, xml_generado, etc.)
   │
   ├── 11. SendBoletaToSiiJob::dispatch(documentoId)
   │       (asíncrono si QUEUE_CONNECTION=database,
   │        síncrono si QUEUE_CONNECTION=sync)
   │
   └── 12. Retornar DocumentoTributario con folio asignado
            → Redirigir a vista de detalle

--- Procesamiento asíncrono (Job) ---

SendBoletaToSiiJob::handle()
   │
   ├── SiiAuthClient::obtenerToken(concessionId)
   │    → SOAP getSemilla + getToken (o cache hit)
   │
   ├── SiiBoletaClient::enviar(xmlSobre, token, config)
   │    → POST multipart con Cookie: TOKEN={token}
   │    → Timeout: connect=10s, transfer=30s
   │    → Retry: 3 intentos con exponential backoff
   │
   ├── SiiResponseParser::parsear(respuesta)
   │    → Extraer STATUS y TRACKID
   │
   ├── Actualizar DocumentoTributario
   │    → estado='enviado', track_id={trackId}
   │
   └── AuditDte::registrar('enviado_sii', detalles)

--- Polling de estados (Scheduled Job) ---

SyncDteStatusJob::handle()
   │
   ├── Buscar documentos con estado='enviado' y track_id
   ├── Para cada uno:
   │    ├── SiiStatusClient::consultarEstado(trackId, token)
   │    ├── Si EPR (procesado): estado='aceptado'
   │    ├── Si RSC/RCT/RFR (rechazado): estado='rechazado'
   │    └── Si SOK (pendiente): sin cambio
   └── AuditDte para cada cambio de estado
```

---

## 7. Seguridad

### 7.1 Certificados Digitales

| Aspecto | Implementación |
|---|---|
| Almacenamiento archivo | `storage/app/dte/certificados/{id_concession}/{uuid}.p12` |
| Nombre archivo | UUID aleatorio (evita path traversal) |
| Permisos archivo | 0640 |
| Contraseña | `Crypt::encryptString()` (AES-256-CBC con APP_KEY) |
| Acceso | Solo via `CafManager` / `DteService`, nunca directo |
| Descifrado | `Crypt::decryptString()` al usar, nunca almacenar descifrado |

### 7.2 CAF

| Aspecto | Implementación |
|---|---|
| XML CAF | Cifrado con `Crypt::encryptString()` en BD |
| Clave privada (RSASK) | Dentro del XML cifrado, nunca extraída en texto plano a BD |
| Descifrado | Al firmar TED, descifrado temporal en memoria |

### 7.3 Multi-tenancy / IDOR

| Control | Implementación |
|---|---|
| Scope de queries | `where('id_concession', auth()->user()->id_concession)` en cada query |
| Validación en controllers | Verificar `$modelo->id_concession === auth()->user()->id_concession` |
| Middleware | Ruta `dte/*` protegida por `auth` + verificación de concesión |
| Permisos | `dte.configuracion`, `dte.certificados`, `dte.folios`, `dte.boletas.ver`, `dte.boletas.emitir`, `dte.boletas.anular`, `dte.consultar` |

### 7.4 Idempotencia

```php
$idempotencyKey = hash('sha256', implode(':', [
    $concessionId,
    $tipoDocumento,
    $ordenServicioId,
    now()->format('Y-m-d\TH:i:s')
]));
```

UNIQUE constraint en `documentos_tributarios.idempotency_key` previene emisiones duplicadas.

---

## 8. Colas y Jobs

### Configuración

```
# .env
QUEUE_CONNECTION=database    # Cambiar de 'sync'
QUEUE_RETRY_AFTER=90
```

Migración `failed_jobs` ya existe en Laravel (viene por defecto).
Migración `jobs` table: ejecutar `php artisan queue:table && php artisan migrate`.

### Jobs

| Job | Queue | Retry | Timeout |
|---|---|---|---|
| `SendBoletaToSiiJob` | `dte-envio` | 3 | 60s |
| `SyncDteStatusJob` | `dte-sync` | 1 | 120s |

### Comando de Sincronización

```php
// app/Console/Kernel.php
$schedule->job(new SyncDteStatusJob)->everyFifteenMinutes();
```

### Worker

```bash
php artisan queue:work --queue=dte-envio,dte-sync --tries=3 --timeout=120
```

---

## 9. Extensibilidad para Futuros Tipos de Documento

La arquitectura soporta nuevos tipos de documento sin cambios estructurales:

| Componente | Qué cambia |
|---|---|
| `DteXmlBuilder` | Nuevo método `construirFactura33()`, `construirNotaCredito61()` |
| `TributaryCalculator` | Facturas usan `calcularDesdeNeto()` (ya implementado) |
| `SiiDteClient` | Nuevo cliente para DTEUpload (facturas usan endpoint confirmado) |
| `documentos_tributarios` | Sin cambio — `tipo_documento` acepta cualquier código |
| `caf_folios` | Sin cambio — un CAF por tipo de documento |
| `DteXmlBuilder` envelope | EnvioDTE en vez de EnvioBOLETA para facturas |

### Notas de Crédito/Débito

Requieren campo `<Referencia>` con tipo de documento referenciado, folio y motivo:

```xml
<Referencia>
  <NroLinRef>1</NroLinRef>
  <TpoDocRef>39</TpoDocRef>      <!-- Tipo del documento que se anula -->
  <FolioRef>123</FolioRef>        <!-- Folio del documento que se anula -->
  <FchRef>2026-10-27</FchRef>
  <CodRef>1</CodRef>              <!-- 1=Anula, 2=Corrige texto, 3=Corrige monto -->
  <RazonRef>Anulación de venta</RazonRef>
</Referencia>
```

El modelo `DocumentoTributario` incluirá relación `referenciaDocumento()` → belongsTo self.

---

## 10. Monitoreo y Alertas

### Dashboard de Facturación

Métricas por concesión:
- Documentos emitidos hoy / esta semana / este mes
- Documentos pendientes de respuesta SII
- Documentos rechazados (requieren atención)
- Folios restantes por tipo de documento
- Certificado: días hasta vencimiento

### Alertas

| Alerta | Condición | Acción |
|---|---|---|
| Folios bajos | < 20% del rango CAF | Email al administrador |
| Certificado por vencer | < 30 días | Email al administrador |
| Documento rechazado | Estado cambia a 'rechazado' | Notificación en UI |
| SII no responde | 3 reintentos fallidos | Log + notificación |
| Folios agotados | folio_actual > folio_hasta | Bloquear emisión + alerta |
