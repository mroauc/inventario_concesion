# Análisis de LibreDTE Core

Fecha: 2026-10-27
Repositorio: https://github.com/LibreDTE/libredte-lib-core
Ubicación local: `/Applications/MAMP/htdocs/libre-dte/libredte-lib-core/`
Documentación: https://core.libredte.cl/docs/index

---

## 1. Identidad del Paquete

| Campo | Valor |
|---|---|
| Nombre | `libredte/libredte-lib-core` |
| Descripción | "LibreDTE: Library for invoicing in Chile (Core)" |
| Licencia | **AGPL-3.0+** (archivo `COPYING`) |
| Homepage | https://core.libredte.cl |
| Total archivos PHP | ~564 |

### Términos Adicionales de Licencia

La licencia AGPL-3.0+ incluye términos adicionales que exigen que cualquier software que utilice LibreDTE:
- Sea también publicado bajo AGPL
- Incluya referencia al proyecto LibreDTE original y su autor

**Riesgo para ROAVAL**: Si se incorpora código de LibreDTE Core, ROAVAL completo debería publicarse como AGPL. Esto es **incompatible con un proyecto comercial cerrado**.

---

## 2. Requisitos de PHP

| Requisito | Valor |
|---|---|
| PHP | **^8.5** (PHP 8.5+) |
| ext-curl | Requerida |
| ext-json | Requerida |
| ext-mbstring | Requerida |
| ext-openssl | Requerida |
| ext-soap | Requerida |

**INCOMPATIBILIDAD CRÍTICA**: ROAVAL ejecuta PHP 8.2.26. LibreDTE Core requiere PHP 8.5+. No se puede integrar directamente sin actualizar PHP, lo cual implicaría riesgos para Laravel 8 y todas las dependencias existentes.

### Dependencias `derafu/*`

Todas las dependencias core están en paquetes `derafu/*` a `dev-main`:

| Paquete | Función |
|---|---|
| `derafu/xml` | Manejo XML (XmlDocument, XmlService) |
| `derafu/signature` | Firma digital XML |
| `derafu/certificate` | Gestión de certificados digitales |
| `derafu/l10n-cl-rut` | Validación RUT chileno |
| `derafu/l10n-cl-enum` | Enums de localización chilena |
| `derafu/backbone` | Framework de aplicación (Package/Component/Worker/Strategy/Job) |
| `derafu/kernel` | MicroKernel (Symfony DI container) |
| `derafu/renderer` | Renderizado de documentos |
| `derafu/mail` | Envío de correo |
| `derafu/cache` | Cache PSR-6 |
| `derafu/config` | Configuración |
| `derafu/repository` | Catálogo de datos |

Todas en `dev-main` → **inestabilidad inherente**.

---

## 3. Arquitectura

### 3.1 Jerarquía

```
Application (MicroKernel)
  └── Package
       ├── System
       │    ├── certificate (carga de certificados)
       │    ├── rendering (renderizado de documentos)
       │    └── repository (catálogo de datos)
       │
       └── Billing
            ├── book (Libros: ventas, compras, boletas, guías, RCOF)
            ├── document (DTE: creación, parsing, normalización, firma)
            ├── exchange (intercambio: envío/recepción SII y email)
            ├── identifier (CAF: gestión y asignación de folios)
            ├── integration (SII: autenticación, upload, consultas)
            ├── ownership_transfer (AEC: cesión electrónica)
            └── trading_parties (Emisor/Receptor/Mandatario)
```

### 3.2 Pipeline de Documentos

`DocumentComponent::bill()` (línea 184):

```
Input Data → ParserWorker::parse() → BuilderWorker::build()
                                        ├── normalize()
                                        ├── sanitize()
                                        ├── validate()
                                        ├── stamp() → TED
                                        └── sign() → XMLDSIG
```

El `DocumentBag` es el carrier central que transporta datos a través de todo el pipeline:
- `inputData` (string raw)
- `parsedData` (array normalizado)
- `normalizedData` (campos calculados)
- `xmlDocument` (XML generado)
- `caf` (CAF para timbre)
- `certificate` (para firma)
- `document` (entidad final)
- `timbre` (array TED)
- `emisor`, `receptor`, `documentType`

---

## 4. Tipos de Documentos Soportados

Enum en `src/Package/Billing/Component/Document/Enum/CodigoDocumento.php`:

| Código | Clase | Archivo |
|---|---|---|
| 33 | `FacturaAfecta` | `Entity/Document/FacturaAfecta.php` |
| 34 | `FacturaExenta` | `Entity/Document/FacturaExenta.php` |
| 39 | `BoletaAfecta` | `Entity/Document/BoletaAfecta.php` |
| 41 | `BoletaExenta` | `Entity/Document/BoletaExenta.php` |
| 43 | `LiquidacionFactura` | `Entity/Document/LiquidacionFactura.php` |
| 46 | `FacturaCompra` | `Entity/Document/FacturaCompra.php` |
| 52 | `GuiaDespacho` | `Entity/Document/GuiaDespacho.php` |
| 56 | `NotaDebito` | `Entity/Document/NotaDebito.php` |
| 61 | `NotaCredito` | `Entity/Document/NotaCredito.php` |
| 110 | `FacturaExportacion` | `Entity/Document/FacturaExportacion.php` |
| 111 | `NotaDebitoExportacion` | `Entity/Document/NotaDebitoExportacion.php` |
| 112 | `NotaCreditoExportacion` | `Entity/Document/NotaCreditoExportacion.php` |

Cada tipo tiene su propio `NormalizerStrategy`, `SanitizerStrategy`, `ValidatorStrategy` y `BuilderStrategy`.

---

## 5. Implementación del TED

**Archivo**: `src/Package/Billing/Component/Document/Abstract/AbstractBuilderStrategy.php`, método `stamp()` (línea 129)

### Proceso

1. Crear draft DTE XML desde datos normalizados
2. Validar folio en rango CAF: `$caf->isEnRango()`
3. Validar vigencia CAF: `$caf->isVigente()`
4. Construir template TED via `AbstractDocument::getTemplateTED()`
5. **Canonicalizar** nodo DD: `C14NEncodedFlattened('/TED/DD')` → ISO-8859-1 C14N
6. **Firmar** DD con clave privada del CAF: `OPENSSL_ALGO_SHA1` (SHA1withRSA)
7. Resultado en `TED/FRMT/@value`

### Estructura TED generada

```
TED (version="1.0")
  DD
    RE → RUT emisor
    TD → Tipo documento
    F  → Folio
    FE → Fecha emisión
    RR → RUT receptor
    RSR → Razón social receptor (max 40 chars)
    MNT → Monto total
    IT1 → Nombre primer item (max 40 chars)
    CAF → Elemento CAF completo
    TSTED → Timestamp
  FRMT (algoritmo="SHA1withRSA")
    @value → Firma base64
```

---

## 6. Gestión de CAF

**Ubicación**: `src/Package/Billing/Component/Identifier/`

### Clases principales

| Clase | Función |
|---|---|
| `Entity/Caf.php` | Entidad CAF (wrapper del XML) |
| `Support/CafFaker.php` | Generador de CAFs fake para testing (IDK=666) |
| `Support/CafFolio.php` | Tracking de folios |
| `Service/CafManager.php` | Servicio de gestión |
| `Worker/CafLoaderWorker.php` | Carga desde XML string |
| `Worker/CafValidatorWorker.php` | Validación de integridad |

### Parsing via XPath

- `//AUTORIZACION/CAF/DA/RE` → RUT emisor
- `//AUTORIZACION/CAF/DA/RS` → Razón social
- `//AUTORIZACION/CAF/DA/TD` → Tipo documento
- `//AUTORIZACION/CAF/DA/RNG/D`, `H` → Rango folios
- `//AUTORIZACION/CAF/DA/FA` → Fecha autorización
- `//AUTORIZACION/CAF/DA/IDK` → Ambiente (300=prod, 100=cert, 666=fake)
- `//AUTORIZACION/RSAPUBK` → Clave pública RSA
- `//AUTORIZACION/RSASK` → **Clave privada RSA**

### Regla de vigencia

Tipos 33, 43, 46, 56, 61 expiran a 180 días desde fecha de autorización.

### Codificación

El CAF XML es **siempre ISO-8859-1**. El método `loadXml()` fuerza la declaración XML a `encoding="ISO-8859-1"`.

---

## 7. Firma XMLDSIG

**Implementación**: Delegada a `derafu/signature` via `SignatureServiceInterface`

### En AbstractBuilderStrategy::sign() (línea 207)

1. Validar que el certificado esté vigente al timestamp de firma
2. Agregar `TmstFirma` al documento
3. Codificar datos a XML via `XmlServiceInterface::encode()`
4. Firmar via `signatureService->signXml($xmlDocument, $certificate, $referenceId)`
5. Cargar XML firmado de vuelta al DocumentBag

### Firma del sobre

`DispatcherWorker::sign()` (línea 525): mismo patrón, firma sobre `SetDTE` usando el ID del envelope.

### Validación de firma

`DispatcherWorker::validateSignature()`: usa `signatureService->validateXml()`.

---

## 8. Comunicación con el SII

### 8.1 Workers de Integración

| Worker | Función |
|---|---|
| `SiiLazyWorker` | Autenticación y consumo SOAP genérico |
| `SiiDteWorker` | Operaciones de documentos (envío, consulta estado) |
| `SiiRcvWorker` | Registro de Compra y Venta (aceptación/rechazo) |
| `SiiRtcWorker` | Transferencia de propiedad (AEC) |

### 8.2 Autenticación (AuthenticateJob)

**Archivo**: `Worker/SiiLazy/Job/AuthenticateJob.php`

Flujo:
1. `getSeedFromSii()` → SOAP `CrSeed::getSeed` (sin certificado, validez 2 min)
2. Crear XML: `<getToken><item><Semilla>{seed}</Semilla></item></getToken>`
3. Firmar XML con certificado digital
4. `GetTokenFromSeed::getToken` con XML firmado
5. Parsear respuesta: `ESTADO '00'` → extraer `TOKEN`
6. **Cachear** token (PSR-6, default TTL 60 segundos)

### 8.3 Envío XML (SendXmlDocumentJob)

**Archivo**: `Worker/SiiDte/Job/SendXmlDocumentJob.php`

- Endpoint: `https://{server}.sii.cl/cgi_dte/UPL/DTEUpload`
- Método: cURL POST multipart
- Campos: `rutSender`, `dvSender`, `rutCompany`, `dvCompany`, `archivo`
- Auth: `Cookie: TOKEN={token}`
- Retry: hasta N intentos con exponential backoff (0.5 * retries segundos)
- Encoding: ISO-8859-1 (forzado)
- Compresión gzip opcional
- Respuesta: XML con `RECEPCIONDTE/STATUS` y `TRACKID`

### 8.4 Consulta de Estado

- `CheckXmlDocumentSentStatusJob` → SOAP `QueryEstUp::getEstUp`
- `ValidateDocumentJob` → SOAP `QueryEstDte::getEstDte`
- `ValidateDocumentSignatureJob` → `QueryEstDteAv`
- `RequestXmlDocumentSentStatusByEmailJob` → `wsDTECorreo`

---

## 9. RCOF (ConsumoFolios / ResumenVentasDiarias)

**Archivo**: `src/Package/Billing/Component/Book/Worker/Builder/Strategy/ResumenVentasDiarias/BuilderStrategy.php`

Construye XML `ConsumoFolios` según schema `ConsumoFolio_v10.xsd`:
- Carátula con RutEmisor, FchInicio, FchFinal, SecEnvio
- Resumen por TipoDocumento: MntNeto, MntIva, TasaIVA, MntExento, MntTotal, FoliosEmitidos, FoliosAnulados, FoliosUtilizados, RangoUtilizados

---

## 10. Componentes NO Implementados

### BoletaSenderStrategy

**Archivo**: `src/Package/Billing/Component/Exchange/Worker/Sender/Strategy/Sii/BoletaSenderStrategy.php`

```php
private function sendEnvelope(EnvelopeInterface $envelope): ExchangeResultInterface {
    // TODO: Implementar el envío de las boletas al SII.
    throw new ExchangeException('The sii.boleta send strategy is not implemented.');
}
```

### DteSenderStrategy

**Archivo**: `src/Package/Billing/Component/Exchange/Worker/Sender/Strategy/Sii/DteSenderStrategy.php`

```php
// TODO: El envío de DTE ya funciona con LazyWorker, esto necesita conectarse.
throw new ExchangeException('The sii.dte send strategy is not implemented.');
```

**Conclusión**: Las estrategias de envío en el componente Exchange son wrappers pendientes. La funcionalidad real de envío existe en el componente Integration (`SiiDteWorker`), pero solo para DTE regulares (facturas, notas, guías), **no para boletas**.

---

## 11. Ambientes SII

Enum `SiiEnvironment` (`src/Package/Billing/Component/Integration/Enum/SiiEnvironment.php`):

| Ambiente | Valor | Servidor DTE | Servidor RCV | CAF IDK |
|---|---|---|---|---|
| PRODUCTION | 0 | `palena.sii.cl` | `ws1.sii.cl` | 300 |
| STAGING | 1 | `maullin.sii.cl` | `ws2.sii.cl` | 100 |

WSDL URL pattern: `https://{server}.sii.cl/DTEWS/{service}.jws?WSDL`

WSDL especiales:
- `QueryEstDteAv`: `https://{server}.sii.cl/DTEWS/services/QueryEstDteAv?WSDL`
- `wsDTECorreo`: `https://{server}.sii.cl/DTEWS/services/wsDTECorreo?WSDL`

Los WSDL de staging están almacenados localmente en `resources/wsdl/maullin/` porque el SII de staging a veces referencia `nogal.sii.cl` (servidor interno no accesible).

---

## 12. Certificados SII

Almacenados en `resources/certificates/`:
- `100.cer` → Certificado SII staging
- `300.cer` → Certificado SII producción

---

## 13. Conclusiones para ROAVAL

### Valor como referencia técnica

1. **Esquemas XSD**: completos y organizados en `resources/schemas/`
2. **Pipeline de documentos**: arquitectura Parse → Build → Normalize → Stamp → Sign
3. **Estructura TED**: template DD con campos y proceso de firma
4. **Autenticación SII**: flujo getSemilla → getToken
5. **Upload DTE**: endpoints, campos, retry con backoff
6. **RCOF**: estructura ConsumoFolios
7. **Validaciones por tipo**: cada tipo tiene normalizer, sanitizer, validator
8. **CAF parsing**: XPath para extraer campos y validar

### NO utilizable directamente

1. **PHP 8.5+**: incompatible con ROAVAL (PHP 8.2)
2. **AGPL-3.0+**: contagia a ROAVAL
3. **Boleta sending**: no implementado
4. **DTE sending**: implementado pero acoplado al ecosistema derafu/*
5. **Ecosistema derafu/***: 12+ paquetes a dev-main, inviable como dependencia

### Recomendación

Usar LibreDTE Core **exclusivamente como referencia técnica** para entender:
- La estructura XML correcta de cada tipo de documento
- El proceso de firma TED (canonicalización, encoding, signing)
- El flujo de autenticación con el SII
- Los esquemas XSD para validación
- La estructura del RCOF

**NO copiar código, NO instalar como dependencia, NO incorporar paquetes derafu/***.
