# Estado de Implementación — Motor DTE ROAVAL

Última actualización: 2026-11-01
Branch: `claude/exciting-sutherland-d5f236`

---

## 1. Fases Completadas

### Fase A — Fundación ✅
- 7 migraciones creadas y ejecutadas
- 7 modelos Eloquent en `app/Models/Dte/`
- CRUDs: ConfiguracionFiscal, Certificados (.p12), CAF (folios)
- Vistas Blade con AdminLTE + Bootstrap 5
- Permisos Spatie: 7 permisos DTE en seeder

### Fase B — Servicios Core ✅
- `app/Services/Dte/Xml/DteXmlBuilder.php` — Construccion XML boleta 39 y EnvioBoleta
- `app/Services/Dte/Xml/TedGenerator.php` — Timbre electronico con firma RSA-SHA1
- `app/Services/Dte/Xml/XmlSigner.php` — XMLDSIG enveloped signatures
- `app/Services/Dte/Tax/TributaryCalculator.php` — Calculo IVA 19%, redondeo SII
- `app/Services/Dte/Folio/FolioAllocator.php` — Asignacion con SELECT...FOR UPDATE
- `app/Services/Dte/Folio/CafManager.php` — Parsing XML CAF, extraccion clave privada
- DTOs: BoletaData, CaratulaData, EmisorData, ReceptorData, DetalleItemData
- `app/Services/Dte/Pdf/DtePdfGenerator.php` — PDF 80mm con DomPDF

### Fase C — Comunicacion SII ✅
- `app/Services/Dte/Sii/SiiAuthClient.php` — SOAP getSemilla/firmarSemilla/getToken
- `app/Services/Dte/Sii/SiiBoletaClient.php` — cURL multipart DTEUpload con retry
- `app/Services/Dte/Sii/SiiResponseParser.php` — Parsing respuestas XML del SII
- `app/Services/Dte/Sii/RcofGenerator.php` — XML ConsumoFolios
- `app/Services/Dte/Sii/RcofSender.php` — Envio RCOF al SII
- `app/Jobs/Dte/SendBoletaToSiiJob.php` — Envio asincrono via queue:database

### Fase D — Orquestacion e UI ✅
- `app/Services/Dte/DteService.php` — Orquestador central (emision + envio sync/async)
- Controllers: BoletaController, BoletaRapidaController, RcofController, DteConsultaController
- Vistas completas en `resources/views/dte/`
- Menu de navegacion con entradas de facturacion

### Fase E — Sincronizacion y Estado ✅
- `app/Console/Commands/SyncDteStatusCommand.php` — Consulta estados al SII
- `app/Console/Commands/SendRcofCommand.php` — Envio RCOF por CLI
- Scheduler: sync cada 15 min, RCOF a las 23:30
- Vista de consulta de estado SII

### Tests Unitarios ✅
- `tests/Unit/Services/Dte/TributaryCalculatorTest.php` — 11 tests
- `tests/Unit/Services/Dte/SiiResponseParserTest.php` — 10 tests
- `tests/Unit/Services/Dte/DteXmlBuilderTest.php` — 14 tests
- `tests/Unit/Services/Dte/TedGeneratorTest.php` — 10 tests
- `tests/Unit/Services/Dte/DteServiceTest.php` — 11 tests

---

## 2. Decisiones Tecnicas Tomadas

### Arquitectura
- **Motor propio** (Opcion A del doc 05): sin dependencia de LibreDTE/AGPL
- Namespace `App\Services\Dte\` con subdirectorios por responsabilidad
- DTOs inmutables para datos de boleta, emisor, receptor, detalles
- Multi-tenancy via `id_concession` en todas las tablas y queries

### XML y Firma
- ISO-8859-1 para encoding XML (requerido por SII)
- `mb_convert_encoding()` en vez de `utf8_decode()` (deprecado PHP 8.2)
- C14N() para canonicalizacion antes de firma
- RSA-SHA1 para TED (clave privada del CAF)
- XMLDSIG enveloped para firma de documento y sobre (certificado .p12)

### Envio al SII
- **Sincrono** para venta rapida (mostrador): resultado inmediato + PDF
- **Asincrono** (jobs) para emision desde orden de servicio: dispatch a queue:database
- Mismo endpoint DTEUpload para boletas y RCOF
- Token SII cacheado por sesion de envio

### Venta Rapida
- Sin OrdenServicio: items ingresados manualmente en formulario
- Receptor opcional (RUT + nombre), default 66666666-6 "Consumidor Final"
- Envio sincrono al SII en el request, PDF disponible inmediato
- Ruta: `/dte/venta-rapida`

### RCOF
- Tres mecanismos: scheduler automatico (23:30), comando artisan, UI web
- Envia ConsumoFolios del dia para todas las concesiones activas
- Ruta UI: `/dte/rcof`

### Queue
- `QUEUE_CONNECTION=database` (tablas `jobs` y `failed_jobs`)
- Migracion de jobs via `php artisan queue:table` (la de Laravel, no custom)

---

## 3. Estructura de Archivos

```
app/
├── Console/Commands/
│   ├── SendRcofCommand.php
│   └── SyncDteStatusCommand.php
├── DTOs/
│   ├── BoletaData.php
│   ├── CaratulaData.php
│   ├── DetalleItemData.php
│   ├── EmisorData.php
│   └── ReceptorData.php
├── Exceptions/
│   └── DteException.php
├── Http/Controllers/Dte/
│   ├── BoletaController.php
│   ├── BoletaRapidaController.php
│   ├── CafController.php
│   ├── CertificadoController.php
│   ├── ConfiguracionFiscalController.php
│   ├── DteConsultaController.php
│   └── RcofController.php
├── Jobs/Dte/
│   └── SendBoletaToSiiJob.php
├── Models/Dte/
│   ├── AuditDte.php
│   ├── CafFolio.php
│   ├── Certificado.php
│   ├── ConfiguracionFiscal.php
│   ├── DocumentoTributario.php
│   ├── DocumentoTributarioDetalle.php
│   └── EnvioSii.php
└── Services/Dte/
    ├── DteService.php
    ├── Folio/
    │   ├── CafManager.php
    │   └── FolioAllocator.php
    ├── Pdf/
    │   └── DtePdfGenerator.php
    ├── Sii/
    │   ├── RcofGenerator.php
    │   ├── RcofSender.php
    │   ├── SiiAuthClient.php
    │   ├── SiiBoletaClient.php
    │   └── SiiResponseParser.php
    ├── Tax/
    │   └── TributaryCalculator.php
    └── Xml/
        ├── DteXmlBuilder.php
        ├── TedGenerator.php
        └── XmlSigner.php

resources/views/dte/
├── boleta_rapida/
│   ├── create.blade.php
│   └── resultado.blade.php
├── boletas/
│   ├── index.blade.php
│   └── show.blade.php
├── cafs/
│   ├── create.blade.php
│   └── index.blade.php
├── certificados/
│   ├── create.blade.php
│   └── index.blade.php
├── configuracion/
│   ├── create.blade.php
│   ├── edit.blade.php
│   └── show.blade.php
├── consulta/
│   └── index.blade.php
└── rcof/
    └── index.blade.php

tests/Unit/Services/Dte/
├── DteServiceTest.php
├── DteXmlBuilderTest.php
├── SiiResponseParserTest.php
├── TedGeneratorTest.php
└── TributaryCalculatorTest.php
```

---

## 4. Rutas Web

| Ruta | Metodo | Permiso | Descripcion |
|---|---|---|---|
| `/dte/configuracion` | GET | dte.configuracion | Config fiscal |
| `/dte/certificados` | GET | dte.certificados | Certificados .p12 |
| `/dte/cafs` | GET | dte.folios | Folios CAF |
| `/dte/venta-rapida` | GET/POST | dte.boletas.emitir | Venta mostrador |
| `/dte/boletas` | GET | dte.boletas.ver | Listado boletas |
| `/dte/consulta` | GET | dte.consultar | Estado SII |
| `/dte/rcof` | GET/POST | dte.consultar | RCOF |

---

## 5. Permisos DTE (en seeder)

- `dte.configuracion` — Administrar config fiscal
- `dte.certificados` — Administrar certificados digitales
- `dte.folios` — Administrar folios CAF
- `dte.boletas.ver` — Ver boletas emitidas
- `dte.boletas.emitir` — Emitir boletas y venta rapida
- `dte.boletas.anular` — Anular boletas (sin implementar)
- `dte.consultar` — Consultar estado SII y RCOF

---

## 6. Pendientes y Trabajo Futuro

### Prioridad Alta (para certificacion SII)
- [ ] **Testing en navegador** con datos de certificacion SII
- [ ] **Certificado digital de certificacion** — necesario para probar envio real
- [ ] **CAF de certificacion** — solicitar en maullin.sii.cl
- [ ] **Set de pruebas SII** — emitir boletas en ambiente certificacion

### Prioridad Media
- [ ] **Anulacion de boletas** — permiso existe, falta controller + service logic
- [ ] **Notas de credito electronicas** (tipo 61) — requiere nuevo builder XML
- [ ] **Alertas de folios por agotarse** — notificacion cuando queda < 20%
- [ ] **Alertas de certificado por vencer** — notificacion 30 dias antes

### Prioridad Baja (fases futuras)
- [ ] Factura electronica (tipo 33/34)
- [ ] Guia de despacho (tipo 52)
- [ ] Libro de Compras y Ventas
- [ ] Intercambio comercial

---

## 7. Restricciones Vigentes

- ❌ No modificar RedAndes ni LibreDTE
- ❌ No copiar codigo AGPL
- ❌ No usar `utf8_decode()` — usar `mb_convert_encoding()`
- ❌ Multi-tenancy obligatorio (`id_concession` en todo)
- ❌ No enviar documentos reales al SII sin autorizacion
- ❌ No ejecutar migraciones sin autorizacion
- ✅ Laravel 8.83.27 / PHP 8.2.26 / MAMP / Apache / macOS

---

## 8. Como Continuar en Otra Sesion

Una nueva sesion de Claude debe:

1. Leer `docs/facturacion/08-estado-implementacion.md` (este archivo)
2. Leer `docs/facturacion/06-arquitectura-propuesta.md` para entender la arquitectura
3. Leer `docs/facturacion/07-plan-implementacion.md` para el plan completo
4. El codigo esta en `app/Services/Dte/`, `app/Http/Controllers/Dte/`, `resources/views/dte/`
5. Los tests estan en `tests/Unit/Services/Dte/`

El prompt inicial para otra sesion podria ser:

> Continua el desarrollo del motor de facturacion electronica SII Chile. Lee `docs/facturacion/08-estado-implementacion.md` para el estado actual. Las fases A-E estan completas. Los pendientes prioritarios son: anulacion de boletas, notas de credito (tipo 61), y alertas de folios. Restricciones en seccion 7 del documento.
