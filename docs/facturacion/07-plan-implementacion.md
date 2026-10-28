# Plan de Implementación

Fecha: 2026-10-27
Proyecto: ROAVAL — Motor de Facturación Electrónica Multiempresa
Decisión: Motor propio (Opción A)

---

## 1. Resumen de Fases

| Fase | Nombre | Semanas | Dependencias | Entregable principal |
|---|---|---|---|---|
| A | Fundación | 1-2 | Ninguna | Migraciones, modelos, CRUD de configuración |
| B | Servicios Core | 3-4 | Fase A | XML, firma, TED, cálculos tributarios |
| C | Comunicación SII | 5-6 | Fase B | Auth, envío, consulta, colas |
| D | Orquestación e UI | 7-8 | Fase C | DteService, controllers, vistas Blade |
| E | Certificación | 9-10 | Fase D | Set de pruebas SII aprobado |

**Punto de decisión**: Al finalizar Fase B, evaluar si XML y firma funcionan correctamente. Si hay problemas fundamentales, considerar pivot a API Comercial (Opción C) antes de invertir en Fase C.

---

## 2. Fase A — Fundación (Semanas 1-2)

### Objetivo
Crear la base de datos y los modelos necesarios, más los CRUDs de administración para configurar cada concesión como emisor de DTE.

### Tareas

#### A.1 — Migraciones de Base de Datos

| Migración | Tabla | Prioridad |
|---|---|---|
| `create_configuracion_fiscal_table` | `configuracion_fiscal` | ALTA |
| `create_dte_certificados_table` | `dte_certificados` | ALTA |
| `create_caf_folios_table` | `caf_folios` | ALTA |
| `create_documentos_tributarios_table` | `documentos_tributarios` | ALTA |
| `create_documento_tributario_detalles_table` | `documento_tributario_detalles` | ALTA |
| `create_envios_sii_table` | `envios_sii` | MEDIA |
| `create_audit_dte_table` | `audit_dte` | MEDIA |

**NO ejecutar migraciones durante fase de investigación**. Las migraciones se crean pero se ejecutan solo con autorización.

#### A.2 — Modelos Eloquent

Crear 7 modelos en `app/Models/Dte/`:
- `ConfiguracionFiscal` — belongsTo Concession, accessor `getCertificadoActivo()`
- `Certificado` — belongsTo Concession, mutator para cifrado de password
- `CafFolio` — belongsTo Concession, scope `disponible()`, método `isVigente()`
- `DocumentoTributario` — belongsTo Concession, OrdenServicio, CafFolio; hasMany Detalles
- `DocumentoTributarioDetalle` — belongsTo DocumentoTributario, Product (nullable), Servicio (nullable)
- `EnvioSii` — belongsTo Concession, DocumentoTributario (nullable)
- `AuditDte` — belongsTo Concession, DocumentoTributario (nullable), User (nullable)

#### A.3 — CRUD de Configuración Fiscal

`ConfiguracionFiscalController`:
- `create/store` — Formulario para ingresar datos fiscales de la concesión
- `edit/update` — Modificar datos fiscales
- `show` — Ver configuración actual
- Validación: RUT formato `12345678-K`, giro max 80 chars, comuna válida

**Permisos**: `dte.configuracion` (solo `super_admin` y `administrador`)

#### A.4 — CRUD de Certificados

`CertificadoController`:
- `create/store` — Upload de archivo .p12 + contraseña
  - Validar que el archivo se puede leer con `openssl_pkcs12_read()`
  - Cifrar contraseña con `Crypt::encryptString()`
  - Guardar en `storage/app/dte/certificados/{id_concession}/{uuid}.p12`
  - Extraer fecha de vencimiento del certificado
- `index` — Listar certificados de la concesión
- `destroy` — Desactivar/eliminar certificado
- Activar/desactivar certificado

**Permisos**: `dte.certificados` (solo `super_admin` y `administrador`)

#### A.5 — CRUD de CAF

`CafController`:
- `create/store` — Upload de archivo XML CAF
  - Parsear XML: extraer RUT, tipo documento, rango, fecha autorización, IDK
  - Validar RUT coincide con concesión
  - Validar IDK coincide con ambiente configurado
  - Validar rango no se solapa con CAF existente
  - Cifrar XML completo con `Crypt::encryptString()`
  - Calcular fecha vencimiento si aplica (tipos 33,43,46,56,61: +180 días)
- `index` — Listar CAFs con folios restantes
- `show` — Detalle del CAF con historial de uso

**Permisos**: `dte.folios` (solo `super_admin` y `administrador`)

#### A.6 — Permisos

Agregar al `RolesAndPermissionsSeeder`:
```
dte.configuracion       → super_admin, administrador
dte.certificados        → super_admin, administrador
dte.folios              → super_admin, administrador
dte.boletas.ver         → super_admin, administrador, operador_servicio
dte.boletas.emitir      → super_admin, administrador, operador_servicio
dte.boletas.anular      → super_admin, administrador
dte.consultar           → super_admin, administrador
```

### Criterios de Aceptación — Fase A

- [ ] 7 migraciones creadas y ejecutables sin error
- [ ] 7 modelos con relaciones, fillable, casts correctos
- [ ] CRUD ConfiguracionFiscal: crear, editar, ver datos fiscales
- [ ] CRUD Certificado: upload .p12, validación openssl, cifrado de contraseña
- [ ] CRUD CAF: upload XML, parsing, validación RUT/IDK/rango, cifrado
- [ ] Permisos DTE agregados al seeder
- [ ] Todos los modelos scoped por id_concession
- [ ] Tests unitarios para modelos (relaciones, scopes, cifrado)

---

## 3. Fase B — Servicios Core (Semanas 3-4)

### Objetivo
Implementar los servicios que generan el XML DTE, calculan impuestos, asignan folios, generan TED y firman documentos. Todo sin comunicación con el SII aún.

### Tareas

#### B.1 — TributaryCalculator

- `calcularDesdeBruto(int $montoTotal)` → para boletas (IVA incluido)
- `calcularDesdeNeto(int $montoNeto)` → para facturas (sin IVA)
- Constante IVA = 19%
- Redondeo a enteros (SII no acepta decimales en montos)

**Tests**: 20+ casos incluyendo edge cases (monto 0, montos grandes, redondeo)

#### B.2 — CafManager

- `cargar(string $xmlCifrado)` → Parsear XML CAF, extraer campos
- `obtenerRsask(CafFolio $caf)` → Extraer clave privada RSA
- `validarRango(int $folio, CafFolio $caf)` → bool
- `isVigente(CafFolio $caf)` → bool (verificar vencimiento)
- `obtenerElementoCAF(CafFolio $caf)` → DOMElement (para insertar en TED)

**Tests**: Parsing con fixture XML de CAF fake, validaciones de rango y vigencia

#### B.3 — FolioAllocator

- `asignar(int $concessionId, int $tipoDocumento)` → ['folio', 'caf']
- Transacción con `lockForUpdate()`
- Si folio_actual > folio_hasta: marcar agotado, intentar siguiente CAF
- Si no hay CAF disponible: `FoliosAgotadosException`

**Tests**: Test de concurrencia (simular 10 solicitudes paralelas, verificar que no hay duplicados)

#### B.4 — DteXmlBuilder

- `construirBoleta39(array $datos, int $folio, ConfiguracionFiscal $config)` → DOMDocument
- `construirEnvioBoleta(array $documentos, ConfiguracionFiscal $config)` → DOMDocument
- Encoding: `<?xml version="1.0" encoding="ISO-8859-1"?>`
- Namespace: `http://www.sii.cl/SiiDte`
- Ids: `DTE_T{tipo}F{folio}`, `DTE_SetDoc`
- Método genérico `construirDetalle(DOMDocument $doc, array $lineas)` reutilizable

**Tests**: Validar XML generado contra `EnvioBOLETA_v11.xsd` y `DTE_v10.xsd`

#### B.5 — TedGenerator

- `generar(array $datos, string $cafXml)` → string (TED XML)
- Flujo: DD → C14N → ISO-8859-1 → colapsar whitespace → SHA1withRSA(RSASK) → FRMT
- Campos DD: RE, TD, F, FE, RR, RSR(max 40), MNT, IT1(max 40), CAF, TSTED
- RSR y IT1 truncados a 40 caracteres

**Tests**: Generar TED con CAF fake, verificar firma con clave pública del mismo CAF

#### B.6 — XmlSigner

- `firmar(DOMDocument $doc, string $referenceId, $privateKey, string $x509Cert)` → void
- DigestValue: `base64_encode(sha1($element->C14N(), true))`
- SignatureValue: `openssl_sign($signedInfo->C14N(), $sig, $privateKey, OPENSSL_ALGO_SHA1)`
- KeyInfo: RSAKeyValue + X509Certificate
- Dos firmas por envío: documento + envelope

**Tests**: Firmar y verificar con `openssl_verify()`, validar estructura Signature

#### B.7 — DTOs

- `BoletaData` — value object para datos de entrada de boleta
- `CaratulaData` — value object para datos de carátula del sobre
- Validación de datos en el DTO (RUT formato, campos requeridos)

### Criterios de Aceptación — Fase B

- [ ] TributaryCalculator: todos los tests de cálculo IVA pasan
- [ ] CafManager: parsing de CAF real (fixture), extracción de RSASK
- [ ] FolioAllocator: test de concurrencia sin folios duplicados
- [ ] DteXmlBuilder: XML de boleta 39 valida contra EnvioBOLETA_v11.xsd
- [ ] TedGenerator: TED firmado, verificable con clave pública del CAF
- [ ] XmlSigner: firma XMLDSIG verificable con openssl_verify()
- [ ] XML completo (documento + TED + firma + envelope + firma envelope) válido contra XSD
- [ ] **PUNTO DE DECISIÓN**: Si los tests de firma y XSD fallan consistentemente, evaluar pivot a Opción C

---

## 4. Fase C — Comunicación SII (Semanas 5-6)

### Objetivo
Conectar con el SII real (ambiente de certificación maullin.sii.cl), implementar autenticación, envío de documentos y consulta de estado.

### Tareas

#### C.1 — SiiAuthClient

- `obtenerToken(int $concessionId)` → string
- SOAP: `CrSeed.jws → getSeed()` → obtener semilla
- Firmar semilla con certificado digital
- SOAP: `GetTokenFromSeed.jws → getToken()` → obtener token
- Cache: Laravel Cache con TTL 3600 segundos (1 hora)
- Retry: 2 intentos con 5 segundos de espera
- Timeouts: connect=10s, transfer=30s

#### C.2 — SiiBoletaClient

- `enviar(string $xmlSobre, string $token, ConfiguracionFiscal $config)` → array
- Mecanismo exacto a verificar en ambiente de certificación
- Retry: 3 intentos con exponential backoff (1s, 2s, 4s)
- Timeouts: connect=10s, transfer=30s
- Headers: User-Agent, Cookie TOKEN

#### C.3 — SiiStatusClient

- `consultarEstado(string $trackId, string $token, ConfiguracionFiscal $config)` → string
- SOAP: `QueryEstUp.jws → getEstUp()`
- Parsear respuesta XML

#### C.4 — SiiResponseParser

- `parsearRespuestaEnvio(string $xml)` → ['status', 'trackId', 'glosa']
- `parsearRespuestaEstado(string $xml)` → ['estado', 'glosa']
- Mapeo de códigos STATUS a mensajes legibles

#### C.5 — Infraestructura de Colas

- Migración `jobs` table: `php artisan queue:table`
- Cambiar `QUEUE_CONNECTION=database` en `.env`
- Crear `SendBoletaToSiiJob` (queue: `dte-envio`, tries: 3, timeout: 60s)
- Crear `SyncDteStatusJob` (queue: `dte-sync`, tries: 1, timeout: 120s)
- Schedule en `Kernel.php`: `SyncDteStatusJob` cada 15 minutos
- Documentar `php artisan queue:work --queue=dte-envio,dte-sync`

#### C.6 — DteStatusSynchronizer

- Buscar documentos con estado='enviado' y track_id no nulo
- Para cada uno: consultar estado al SII
- Actualizar estado según respuesta
- Registrar en audit_dte

#### C.7 — Tests de Integración con maullin.sii.cl

- Autenticación con certificado real de certificación
- Envío de boleta 39 al SII de certificación
- Consulta de estado
- Manejo de errores (token expirado, schema inválido, firma rechazada)

### Criterios de Aceptación — Fase C

- [ ] SiiAuthClient obtiene token válido de maullin.sii.cl
- [ ] Boleta 39 enviada y aceptada por maullin.sii.cl
- [ ] Estado consultado correctamente via SOAP
- [ ] Jobs se ejecutan con queue:work
- [ ] Retry funciona ante errores transitorios
- [ ] AuditDte registra cada interacción con el SII

---

## 5. Fase D — Orquestación e UI (Semanas 7-8)

### Objetivo
Integrar todos los servicios en el orquestador DteService, crear los controllers y vistas Blade para la operación diaria.

### Tareas

#### D.1 — DteService (Orquestador)

- Implementa `FacturadorInterface`
- Método `emitir()`: coordina todo el flujo (13 pasos)
- Método `anular()`: emite nota de crédito referenciando el documento original
- Método `consultarEstado()`: consulta estado puntual
- Manejo de excepciones con mensajes claros para el usuario

#### D.2 — BoletaController

- `index` — Listado de boletas con filtros (fecha, estado, folio)
- `create` — Formulario de emisión (desde orden de servicio o manual)
- `store` — Emitir boleta via DteService::emitir()
- `show` — Detalle de boleta con estado SII, XML, TED
- `pdf` — Descargar PDF de la boleta
- `consultarEstado` — Forzar consulta de estado al SII

#### D.3 — Vistas Blade

| Vista | Contenido |
|---|---|
| `dte/boletas/index.blade.php` | DataTable con boletas, filtros, badges de estado |
| `dte/boletas/create.blade.php` | Formulario: receptor, líneas de detalle, totales |
| `dte/boletas/show.blade.php` | Detalle completo, estado SII, acciones |
| `dte/configuracion/index.blade.php` | Datos fiscales de la concesión |
| `dte/certificados/index.blade.php` | Listado de certificados con estado |
| `dte/cafs/index.blade.php` | Listado de CAFs con folios restantes |

Estilo: AdminLTE + Bootstrap 5 (consistente con ROAVAL existente)

#### D.4 — Botón "Emitir Boleta" en Orden de Servicio

- Agregar botón en vista `ordenes_servicio/show.blade.php`
- Pre-llenar datos de la boleta desde la orden (cliente, detalle, montos)
- Mostrar estado de DTE asociado en el listado de órdenes

#### D.5 — DtePdfGenerator

- Basado en `barryvdh/laravel-dompdf` (ya instalado)
- Incluir:
  - Logo y datos del emisor
  - Datos del receptor
  - Detalle de productos/servicios
  - Totales (neto, IVA, total)
  - Código de barras PDF417 del TED
  - Folio y fecha
  - Timbre SII

Nota: PDF417 requiere librería de barcode. Evaluar `picqer/php-barcode-generator` o `milon/barcode`.

#### D.6 — Rutas

```php
Route::prefix('dte')->middleware(['auth'])->group(function () {
    Route::resource('configuracion', ConfiguracionFiscalController::class);
    Route::resource('certificados', CertificadoController::class);
    Route::resource('cafs', CafController::class);
    Route::resource('boletas', BoletaController::class);
    Route::get('boletas/{boleta}/pdf', [BoletaController::class, 'pdf']);
    Route::post('boletas/{boleta}/consultar-estado', [BoletaController::class, 'consultarEstado']);
    Route::get('consulta', [DteConsultaController::class, 'index']);
});
```

#### D.7 — Menú de Navegación

Agregar sección "Facturación" en sidebar de AdminLTE:
- Configuración Fiscal
- Certificados
- Folios (CAF)
- Boletas
- Consulta SII

### Criterios de Aceptación — Fase D

- [ ] Emitir boleta desde formulario web → documento aparece en listado
- [ ] Emitir boleta desde orden de servicio → datos pre-llenados
- [ ] Estado SII visible en listado y detalle
- [ ] PDF de boleta descargable con TED (código de barras)
- [ ] CRUD de configuración fiscal funcional
- [ ] CRUD de certificados funcional (upload, validación, cifrado)
- [ ] CRUD de CAF funcional (upload, parsing, validación)
- [ ] Permisos aplicados correctamente por rol
- [ ] Multi-tenancy: cada concesión ve solo sus datos
- [ ] Test funcional end-to-end: crear config → subir cert → subir CAF → emitir boleta

---

## 6. Fase E — Certificación y Producción (Semanas 9-10)

### Objetivo
Completar el proceso de certificación del SII para al menos una concesión y pasar a producción.

### Tareas

#### E.1 — Generador RCOF

- Implementar `RcofGenerator` para generar ConsumoFolios
- Estructura: `ConsumoFolios > DocumentoConsumoFolios > Caratula + Resumen[]`
- Firma XMLDSIG del reporte
- Validar contra `ConsumoFolio_v10.xsd`

#### E.2 — Set de Pruebas SII

1. Solicitar set de prueba al SII (vía portal maullin.sii.cl)
2. Generar documentos de prueba según requisitos del set
3. Enviar todos los documentos de prueba al SII certificación
4. Generar RCOF diario de las boletas emitidas
5. Generar PDFs de muestra para enviar al SII

#### E.3 — Envío para Certificación

1. Enviar documentos generados y RCOF al SII
2. Enviar muestras impresas (PDFs) si lo requieren
3. Esperar revisión del SII (10-15 días hábiles)
4. Corregir observaciones si las hay

#### E.4 — Transición a Producción

1. Recibir resolución del SII (FchResol, NroResol)
2. Actualizar configuración fiscal con datos de resolución
3. Cambiar ambiente de `certificacion` a `produccion`
4. Solicitar folios de producción (CAF con IDK=300)
5. Subir CAF de producción

#### E.5 — Monitoreo Post-Producción

- Verificar que las boletas se emiten y envían correctamente
- Monitorear estados del SII
- Verificar alerta de folios bajos
- Verificar alerta de certificado por vencer

### Criterios de Aceptación — Fase E

- [ ] Set de pruebas SII completado sin errores
- [ ] RCOF generado y enviado correctamente
- [ ] Resolución SII recibida
- [ ] Primera boleta emitida en producción y aceptada por el SII
- [ ] Dashboard de monitoreo funcional
- [ ] Alertas de folios y certificados configuradas

---

## 7. Fases Futuras (Post-Producción)

| Fase | Tipo Documento | Estimación | Dependencia |
|---|---|---|---|
| F | Factura electrónica (33/34) | 2 semanas | Fase E |
| G | Nota de crédito/débito (56/61) | 1 semana | Fase F |
| H | Guía de despacho (52) | 1 semana | Fase F |
| I | Libro de Compras y Ventas | 2 semanas | Fase F |
| J | Intercambio comercial (acuse recibo) | 2 semanas | Fase F |

---

## 8. Matriz de Riesgos

| # | Riesgo | P | S | Mitigación | Contingencia |
|---|---|---|---|---|---|
| 1 | Contaminación AGPL | BAJA | CRÍTICA | Cero copy-paste de LibreDTE. Solo referencia de patrones. | Auditoría de código pre-release |
| 2 | PHP 8.2 / Laravel 8 EOL | MEDIA | MEDIA | Módulo usa solo features estables. | Planificar upgrade por separado |
| 3 | `saveHTML()` vs `C14N()` | BAJA | MEDIA | Implementar con C14N() correcto. | Fallback saveHTML() documentado |
| 4 | Race condition en folios | ALTA→BAJA | ALTA | `SELECT...FOR UPDATE` (probado) | — |
| 5 | Certificación SII demora | MEDIA | MEDIA | Iniciar temprano con staging | Tramitar con urgencia |
| 6 | Certificados expuestos | BAJA | CRÍTICA | `Crypt::encryptString()` | — |
| 7 | Endpoints boleta no confirmados | MEDIA | MEDIA | Verificar en Fase C | Pivot a Opción C |
| 8 | Worker no ejecutándose | ALTA | ALTA | Monitoreo + supervisor/systemd | Fallback sync para emergencia |
| 9 | Folios agotados | MEDIA | ALTA | Alerta al 80%, dashboard | Solicitar CAF de emergencia |
| 10 | SII cambia protocolo | BAJA | MEDIA | Modularidad, monitoreo | Adaptar cliente afectado |

---

## 9. Restricciones Recordatorias

Las siguientes restricciones aplican durante **toda** la implementación:

- ❌ No modificar RedAndes
- ❌ No modificar LibreDTE Core
- ❌ No instalar automáticamente LibreDTE en ROAVAL
- ❌ No copiar código AGPL sin análisis de licencia
- ❌ No modificar lógica comercial existente
- ❌ No actualizar PHP o Laravel sin autorización
- ❌ No ejecutar migraciones sin autorización
- ❌ No tocar datos productivos
- ❌ No enviar documentos reales al SII
- ❌ No asumir que el código existente es correcto
- ❌ No inventar protocolos ni endpoints
- ❌ No ocultar incertidumbres técnicas
- ❌ **No continuar a la fase de implementación sin aprobación del usuario**

---

## 10. Próximos Pasos Inmediatos

1. **Revisar estos 7 documentos** — verificar que la información es completa y correcta
2. **Aprobar la arquitectura propuesta** — confirmar Opción A (Motor Propio)
3. **Definir prioridad de fases** — confirmar orden A→B→C→D→E
4. **Autorizar inicio de Fase A** — creación de migraciones y modelos
5. **Proveer certificado digital de certificación** — necesario para Fase C
6. **Solicitar CAF de certificación** — necesario para testing en maullin.sii.cl
