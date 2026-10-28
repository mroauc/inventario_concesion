# Auditoría de RedAndes ERP

Fecha: 2026-10-27
Proyecto: RedAndes ERP — Sistema de facturación electrónica existente
Ubicación local: `/Applications/MAMP/htdocs/RedAndes/`

---

## 1. Resumen Ejecutivo

RedAndes es un ERP Laravel que tiene integración SII funcional para DTE (facturas, notas de crédito/débito, guías de despacho, facturas de exportación). La implementación es **hand-rolled** usando DOMDocument, SoapClient, cURL y openssl directamente, sin dependencia de LibreDTE ni otras librerías DTE.

**Hallazgo crítico**: RedAndes **NO soporta boletas electrónicas (tipo 39/41)**. Las referencias a "boleta" en el código son exclusivamente "boleta de honorarios" (un tipo de documento distinto). Esto significa que RedAndes **no puede servir como referencia para el flujo de boletas**, pero sí para los patrones generales de autenticación, firma XML, TED y envío al SII.

---

## 2. Tipos de Documentos Soportados

| Código | Tipo | Estado en RedAndes |
|---|---|---|
| 33 | Factura electrónica | ✅ Implementado |
| 34 | Factura electrónica exenta | ✅ Implementado |
| 46 | Factura de compra electrónica | ✅ Implementado |
| 52 | Guía de despacho electrónica | ✅ Implementado |
| 56 | Nota de débito electrónica | ✅ Implementado |
| 61 | Nota de crédito electrónica | ✅ Implementado |
| 110 | Factura de exportación | ✅ Implementado |
| 111 | Nota de débito exportación | ✅ Implementado |
| 112 | Nota de crédito exportación | ✅ Implementado |
| **39** | **Boleta electrónica afecta** | **❌ NO implementado** |
| **41** | **Boleta electrónica exenta** | **❌ NO implementado** |

---

## 3. Clasificación de Componentes

### 3.1 Autenticación SII

**Archivos**: `TokenDTEController`, servicios de autenticación

**Flujo implementado**:
1. `getSemilla()` — SOAP `CrSeed.jws?WSDL` → `getSeed()`
2. Parsear respuesta XML → extraer `<SEMILLA>`
3. Construir XML: `<getToken><item><Semilla>{semilla}</Semilla></item></getToken>`
4. Firmar XML con certificado digital del usuario
5. `getToken()` — SOAP `GetTokenFromSeed.jws?WSDL` → `getToken(pszXml)`
6. Parsear respuesta → extraer `<TOKEN>` cuando `ESTADO == '00'`

**Calificación**: ⚠️ Patrón correcto, bugs puntuales

**Defectos encontrados**:
- **Bug de doble semilla**: `TokenDTEController` obtiene semilla dos veces (líneas ~73 y ~103), descartando la primera. El código funciona pero desperdicia una llamada SOAP.
- Manejo de errores básico (verifica ESTADO pero no todos los códigos de error)

### 3.2 Construcción XML DTE

**Patrón**: DOMDocument directo

```
DOMDocument → createElement/appendChild
  → DTE (version="1.0")
    → Documento (ID="DTE_T{tipo}F{folio}")
      → Encabezado (IdDoc, Emisor, Receptor, Totales)
      → Detalle[] (NroLinDet, NmbItem, QtyItem, PrcItem, MontoItem)
      → DscRcgGlobal[] (si aplica)
      → Referencia[] (para notas de crédito/débito)
      → TED
      → TmstFirma
```

**Calificación**: ✅ Estructura correcta

El XML generado sigue la estructura oficial del SII. La construcción campo-por-campo con DOMDocument es verbosa pero correcta. Cada tipo de documento tiene sus campos específicos.

### 3.3 Timbre Electrónico Digital (TED)

**Flujo implementado**:
1. Construir nodo `<DD>` con RE, TD, F, FE, RR, RSR, MNT, IT1, CAF, TSTED
2. Canonicalizar DD
3. Firmar con clave privada del CAF: `openssl_sign($dd, $firma, $rsask, OPENSSL_ALGO_SHA1)`
4. Base64-encode la firma
5. Insertar `<FRMT algoritmo="SHA1withRSA">{firma}</FRMT>` en TED

**Calificación**: ⚠️ Funcional con reservas sobre canonicalización

La estructura TED es correcta. El proceso de firma usa SHA1withRSA como corresponde. La reserva es sobre el paso de canonicalización — ver defecto #1 en sección 4.

### 3.4 Firma XMLDSIG

**Flujo implementado**:
1. Computar DigestValue de la referencia (el tag firmado)
2. Construir `<SignedInfo>` con CanonicalizationMethod, SignatureMethod, Reference
3. Computar SignatureValue sobre SignedInfo
4. Agregar KeyInfo con Modulus, Exponent, X509Certificate

**Calificación**: ⚠️ Funcional pero con defecto de canonicalización

**Defecto crítico**: Para obtener el contenido de `SignedInfo` para firmar, RedAndes usa `saveHTML()` en lugar de `C14N()`. `saveHTML()` es un serializador HTML (no XML), lo que técnicamente desvía del estándar XMLDSIG que exige canonicalización C14N. El hecho de que funciona en producción sugiere que el SII es tolerante en este punto, pero es una desviación que podría causar problemas futuros.

### 3.5 Envelope (EnvioDTE)

**Estructura**:
```
EnvioDTE (xmlns, version="1.0")
  → SetDTE (ID)
    → Caratula (version="1.0")
      → RutEmisor, RutEnvia, RutReceptor(=60803000-K)
      → FchResol, NroResol, TmstFirmaEnv
      → SubTotDTE (TpoDTE, NroDTE)
    → DTE[] (documentos firmados)
  → Signature (firma del SetDTE)
```

**Calificación**: ✅ Correcto

**Nota**: Solo usa `EnvioDTE`, no `EnvioBOLETA`. No hay implementación del sobre específico para boletas.

### 3.6 Envío al SII

**Método**: cURL POST multipart a `/cgi_dte/UPL/DTEUpload`

**Campos**:
- `rutSender`, `dvSender`: RUT del certificado
- `rutCompany`, `dvCompany`: RUT de la empresa
- `archivo`: archivo XML (multipart file)

**Headers**: `Cookie: TOKEN={token}`, User-Agent, Referer

**Calificación**: ⚠️ Funcional con defectos

**Defectos**:
- Sin timeout en cURL — puede bloquear workers indefinidamente
- Referer hardcodeado
- Sin retry ni exponential backoff
- Sin compresión gzip

### 3.7 Consulta de Estado

**SOAP**: `QueryEstUp.jws?WSDL` → `getEstUp(rut, dv, trackId, token)`

Parsea la respuesta XML para extraer el estado del envío.

**Calificación**: ⚠️ Funcional

**Defecto**: Variable `$ambiente` indefinida en `ConsultaEnvioDTE` — bug que podría causar error en ejecución.

### 3.8 Intercambio Comercial

RedAndes implementa el intercambio comercial completo:
- `RecepcionDTE` — acuse de recibo
- `ResultadoDTE` — resultado aceptación/rechazo
- `EnvioRecibosDTE` — envío de recibos

**Calificación**: ✅ Funcional (no relevante para boletas)

El intercambio comercial aplica solo a facturas, no a boletas. Sin embargo, será útil como referencia cuando se implemente factura 33/34 en fases futuras.

### 3.9 Libro de Compras y Ventas

Generación del libro de compras y ventas electrónico.

**Calificación**: ✅ Implementado (futura referencia)

### 3.10 Gestión de Certificados

**Almacenamiento**: Archivos .pfx/.p12 almacenados en disco

**Calificación**: ❌ Defecto de seguridad

**Defecto crítico**: Las contraseñas de certificados digitales se almacenan en **texto plano** en la base de datos. Esto es un riesgo de seguridad severo — cualquier acceso a la BD expone las contraseñas de los certificados, que permiten firmar documentos tributarios legalmente válidos.

### 3.11 Gestión de Folios (CAF)

**Implementación**: Parse del XML CAF para extraer rango, clave privada, validación de IDK.

**Calificación**: ❌ Race condition

**Defecto**: La asignación de folios **no tiene locking**. En un escenario de concurrencia (dos usuarios emitiendo documentos simultáneamente), pueden asignarse el mismo folio a dos documentos distintos, lo que causa rechazo por el SII.

---

## 4. Defectos Críticos — Resumen Completo

### Defecto 1: saveHTML() en lugar de C14N()

**Severidad**: MEDIA
**Ubicación**: Lógica de firma XMLDSIG
**Descripción**: Usa `saveHTML()` para serializar `SignedInfo` antes de firmar, en vez del estándar `C14N()` (Canonical XML).
**Impacto**: Funciona actualmente pero es una desviación del estándar XMLDSIG. Podría dejar de funcionar si el SII cambia su validación.
**Para ROAVAL**: Implementar con `C14N()` correcto. Si el SII rechaza, probar `saveHTML()` como fallback documentado.

### Defecto 2: Bug de doble semilla

**Severidad**: BAJA
**Ubicación**: `TokenDTEController` (líneas ~73 y ~103)
**Descripción**: Obtiene semilla del SII dos veces (via SOAP), descartando la primera llamada.
**Impacto**: Desperdicio de una llamada SOAP, pero no afecta funcionalidad.
**Para ROAVAL**: Obtener semilla una sola vez.

### Defecto 3: Contraseñas de certificados en texto plano

**Severidad**: CRÍTICA
**Ubicación**: Tabla de certificados en BD
**Descripción**: Las contraseñas de los certificados digitales .p12 se almacenan sin cifrar.
**Impacto**: Cualquier breach de BD expone la capacidad de firmar documentos tributarios.
**Para ROAVAL**: Usar `Crypt::encryptString()` (AES-256-CBC) desde el día uno.

### Defecto 4: Race condition en asignación de folios

**Severidad**: ALTA
**Ubicación**: Lógica de asignación de folios
**Descripción**: No hay mecanismo de locking al asignar folios. Múltiples procesos pueden leer el mismo folio_actual y asignarlo a documentos distintos.
**Impacto**: Folios duplicados → rechazo SII, pérdida de folios autorizados.
**Para ROAVAL**: `SELECT ... FOR UPDATE` dentro de transacción (patrón existente en `OrdenServicioController`).

### Defecto 5: Valores hardcodeados

**Severidad**: MEDIA
**Ubicación**: Múltiples controllers
**Descripción**: Fecha de resolución SII, email de envío, referer HTTP hardcodeados en el código.
**Impacto**: Cada empresa necesita su propia fecha de resolución. Hardcodeado impide multi-empresa.
**Para ROAVAL**: Todos estos valores en `configuracion_fiscal` por concesión.

### Defecto 6: dd() en código de colas

**Severidad**: ALTA
**Ubicación**: Jobs de cola
**Descripción**: Llamadas a `dd()` (dump and die) en código que se ejecuta en queue workers.
**Impacto**: Mata el worker de colas, deteniendo todo el procesamiento asíncrono.
**Para ROAVAL**: Usar `Log::info()` / `Log::error()` exclusivamente en jobs.

### Defecto 7: Sin timeouts en cURL

**Severidad**: MEDIA
**Ubicación**: Envío al SII via cURL
**Descripción**: Las llamadas cURL al SII no tienen `CURLOPT_TIMEOUT` ni `CURLOPT_CONNECTTIMEOUT`.
**Impacto**: Si el SII no responde, el proceso queda bloqueado indefinidamente.
**Para ROAVAL**: `CURLOPT_CONNECTTIMEOUT = 10`, `CURLOPT_TIMEOUT = 30` (o usar Guzzle con timeouts).

### Defecto 8: utf8_decode() deprecado

**Severidad**: BAJA
**Ubicación**: Conversiones de encoding
**Descripción**: Usa `utf8_decode()` que está deprecado desde PHP 8.2 y genera E_DEPRECATED.
**Impacto**: Warnings en logs, eventual remoción en PHP futuro.
**Para ROAVAL**: Usar `mb_convert_encoding($str, 'ISO-8859-1', 'UTF-8')`.

### Defecto 9: Variable indefinida en ConsultaEnvioDTE

**Severidad**: MEDIA
**Ubicación**: Controller de consulta de estado
**Descripción**: Variable `$ambiente` utilizada sin definir — puede causar error de ejecución.
**Para ROAVAL**: Ambiente definido en `configuracion_fiscal` y validado en runtime.

### Defecto 10: Duplicación masiva de código

**Severidad**: MEDIA
**Ubicación**: Todo el proyecto
**Descripción**:
- Lectura de certificados digitales duplicada en al menos 5 archivos distintos
- Lógica de firma duplicada en al menos 3 archivos
- Construcción XML duplicada para tipos similares de DTE
**Para ROAVAL**: Servicios dedicados: `CafManager`, `XmlSigner`, `DteXmlBuilder`, `SiiAuthClient`.

---

## 5. Componentes Reutilizables como Referencia

### Patrones a seguir (no copiar código)

| Componente | Qué aprender | Archivo(s) referencia |
|---|---|---|
| Auth SII | Flujo getSemilla → firmar → getToken | TokenDTEController |
| XML DTE | Estructura DOMDocument para cada tipo de DTE | Controllers de generación |
| TED | Campos DD, firma SHA1withRSA con CAF RSASK | Generación de timbre |
| Envelope | EnvioDTE con Carátula y SetDTE | Armado de sobre |
| Upload SII | POST multipart con TOKEN | Envío de documentos |
| Status | SOAP QueryEstUp para polling | Consulta de estado |
| IECV | Libro de compras/ventas | Generación de libros |
| Intercambio | RecepcionDTE, ResultadoDTE, EnvioRecibos | Intercambio comercial |

### Patrones a NO seguir

| Anti-patrón | Corrección en ROAVAL |
|---|---|
| saveHTML() para firmar | Usar C14N() |
| Contraseñas en texto plano | Crypt::encryptString() |
| Sin locking de folios | SELECT ... FOR UPDATE |
| Sin timeouts | Guzzle con connect_timeout + timeout |
| dd() en jobs | Log::error() + throw exception |
| utf8_decode() | mb_convert_encoding() |
| Código duplicado | Servicios con responsabilidad única |
| Valores hardcodeados | ConfiguracionFiscal por concesión |

---

## 6. Conclusiones para ROAVAL

### Valor de RedAndes

1. **Prueba de factibilidad**: Demuestra que DOMDocument + openssl + SoapClient + cURL son suficientes para integración SII completa en PHP 8.x
2. **Estructura XML correcta**: Los documentos generados son aceptados por el SII
3. **Referencia de campos**: Cada tipo de DTE con sus campos obligatorios y opcionales
4. **Flujo completo documentado**: Auth → Build → Sign → Stamp → Envelope → Send → Check

### Limitaciones para ROAVAL

1. **Sin boletas (39/41)**: El componente más prioritario de ROAVAL no tiene referencia en RedAndes
2. **Solo EnvioDTE, no EnvioBOLETA**: El sobre para boletas es distinto
3. **Defectos de seguridad**: No imitar el manejo de certificados ni la asignación de folios
4. **Código AGPL**: No verificada la licencia exacta, pero el código no debe copiarse — solo servir como referencia de patrones

### Recomendación

Usar RedAndes **exclusivamente como referencia de patrones** para:
- Entender la secuencia de llamadas SOAP al SII
- Conocer la estructura de campos de cada tipo de DTE
- Validar que DOMDocument + openssl son herramientas viables en PHP 8.2
- Identificar anti-patrones a evitar

**NO copiar código. NO incorporar ningún archivo. NO usar como dependencia.**
