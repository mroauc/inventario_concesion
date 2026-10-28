# Investigación de Requisitos del SII

Fecha: 2026-10-27
Fuente principal: https://www.sii.cl/
Punto de partida: https://www.sii.cl/servicios_online/3532-instructivo_tecnico_be-3811.html

---

## 1. Tipos de Documentos Tributarios Electrónicos (DTE)

| Código | Tipo | Prioridad en ROAVAL |
|---|---|---|
| 39 | Boleta electrónica afecta | **PRIMERA** |
| 41 | Boleta electrónica exenta | Segunda |
| 33 | Factura electrónica | Tercera |
| 34 | Factura electrónica exenta | Tercera |
| 61 | Nota de crédito electrónica | Tercera |
| 56 | Nota de débito electrónica | Futura |
| 52 | Guía de despacho electrónica | Futura |
| 43 | Liquidación factura electrónica | No aplica |
| 46 | Factura de compra electrónica | No aplica |
| 110/111/112 | Documentos de exportación | No aplica |

---

## 2. Esquemas XSD Oficiales

Los esquemas XSD definen la estructura válida de cada documento. Versiones encontradas en LibreDTE Core (`resources/schemas/`):

| Esquema | Versión | Uso |
|---|---|---|
| `DTE_v10.xsd` | 1.0 | Documento DTE individual |
| `EnvioDTE_v10.xsd` | 1.0 | Sobre de envío de DTE (facturas, notas, guías) |
| `EnvioBOLETA_v11.xsd` | 1.1 | Sobre de envío de boletas |
| `ConsumoFolio_v10.xsd` | 1.0 | Reporte de consumo de folios (RCOF) |
| `xmldsignature_v10.xsd` | 1.0 | Firma digital XML |
| `SiiTypes_v10.xsd` | 1.0 | Tipos de datos SII |
| `LibroBOLETA_v10.xsd` | 1.0 | Libro de boletas |
| `LibroCV_v10.xsd` | 1.0 | Libro de compras/ventas |

**Importante**: Las boletas usan `EnvioBOLETA_v11.xsd` (versión 1.1), distinto de `EnvioDTE_v10.xsd`. Esto refleja que el envío de boletas tiene un mecanismo separado del envío de DTE.

---

## 3. Estructura XML de un DTE

### 3.1 Documento Individual

```xml
<?xml version="1.0" encoding="ISO-8859-1"?>
<DTE version="1.0" xmlns="http://www.sii.cl/SiiDte">
  <Documento ID="DTE_T{tipo}F{folio}">
    <Encabezado>
      <IdDoc>
        <TipoDTE>39</TipoDTE>
        <Folio>123</Folio>
        <FchEmis>2026-10-27</FchEmis>
        <IndServicio>3</IndServicio>     <!-- 1=venta bienes, 3=venta servicios -->
      </IdDoc>
      <Emisor>
        <RUTEmisor>76123456-K</RUTEmisor>
        <RznSocEmisor>Empresa S.A.</RznSocEmisor>
        <GiroEmisor>Servicio técnico</GiroEmisor>   <!-- Max 40 chars -->
        <DirOrigen>Av. Principal 100</DirOrigen>
        <CmnaOrigen>Santiago</CmnaOrigen>
        <CiudadOrigen>Santiago</CiudadOrigen>
      </Emisor>
      <Receptor>
        <RUTRecep>12345678-9</RUTRecep>  <!-- o 66666666-6 para anónimo -->
        <RznSocRecep>Juan Pérez</RznSocRecep>
        <DirRecep>Calle 123</DirRecep>
        <CmnaRecep>Providencia</CmnaRecep>
      </Receptor>
      <Totales>
        <MntNeto>8403</MntNeto>
        <IVA>1597</IVA>
        <MntTotal>10000</MntTotal>
      </Totales>
    </Encabezado>
    <Detalle>
      <NroLinDet>1</NroLinDet>
      <NmbItem>Servicio de mantención</NmbItem>
      <QtyItem>1</QtyItem>
      <PrcItem>10000</PrcItem>
      <MontoItem>10000</MontoItem>
    </Detalle>
    <TED version="1.0">
      <!-- Timbre Electrónico Digital -->
    </TED>
    <TmstFirma>2026-10-27T14:30:00</TmstFirma>
  </Documento>
  <Signature xmlns="http://www.w3.org/2000/09/xmldsig#">
    <!-- Firma XMLDSIG -->
  </Signature>
</DTE>
```

### 3.2 Sobre de Envío (EnvioBOLETA para boletas)

```xml
<?xml version="1.0" encoding="ISO-8859-1"?>
<EnvioBOLETA xmlns="http://www.sii.cl/SiiDte" version="1.0">
  <SetDTE ID="DTE_SetDoc">
    <Caratula version="1.0">
      <RutEmisor>76123456-K</RutEmisor>
      <RutEnvia>11111111-1</RutEnvia>      <!-- RUT del certificado digital -->
      <RutReceptor>60803000-K</RutReceptor> <!-- SIEMPRE el RUT del SII -->
      <FchResol>2024-01-15</FchResol>       <!-- Fecha resolución autorización -->
      <NroResol>0</NroResol>                <!-- 0 para certificación -->
      <TmstFirmaEnv>2026-10-27T14:30:00</TmstFirmaEnv>
      <SubTotDTE>
        <TpoDTE>39</TpoDTE>
        <NroDTE>1</NroDTE>
      </SubTotDTE>
    </Caratula>
    <DTE version="1.0">
      <!-- Documento(s) individual(es) firmados -->
    </DTE>
  </SetDTE>
  <Signature xmlns="http://www.w3.org/2000/09/xmldsig#">
    <!-- Firma del sobre (SetDTE) -->
  </Signature>
</EnvioBOLETA>
```

**Diferencia clave**: EnvioBOLETA vs EnvioDTE. Las boletas usan su propio tipo de sobre.

---

## 4. Codificación

**TODO el XML del SII debe ser ISO-8859-1**, tanto el encoding declaration como el contenido. Esto incluye:
- Declaración XML: `<?xml version="1.0" encoding="ISO-8859-1"?>`
- El contenido del documento
- Los CAF (siempre ISO-8859-1)
- Las respuestas del SII

En PHP, se debe convertir de UTF-8 (encoding interno) a ISO-8859-1 antes de enviar:
```php
mb_convert_encoding($string, 'ISO-8859-1', 'UTF-8')
```

**NOTA**: `utf8_decode()` está deprecado desde PHP 8.2. Usar `mb_convert_encoding()`.

---

## 5. Timbre Electrónico Digital (TED)

El TED es la prueba de autenticidad de un DTE. Se incluye dentro del documento XML y se usa para generar el código de barras PDF417.

### 5.1 Estructura TED

```xml
<TED version="1.0">
  <DD>
    <RE>76123456-K</RE>           <!-- RUT emisor -->
    <TD>39</TD>                    <!-- Tipo documento -->
    <F>123</F>                     <!-- Folio -->
    <FE>2026-10-27</FE>           <!-- Fecha emisión -->
    <RR>12345678-9</RR>           <!-- RUT receptor -->
    <RSR>Juan Pérez</RSR>         <!-- Razón social receptor (max 40 chars) -->
    <MNT>10000</MNT>              <!-- Monto total -->
    <IT1>Servicio de mantención</IT1>  <!-- Primer item (max 40 chars) -->
    <CAF version="1.0">
      <!-- CAF completo del rango autorizado -->
    </CAF>
    <TSTED>2026-10-27T14:30:00</TSTED>  <!-- Timestamp -->
  </DD>
  <FRMT algoritmo="SHA1withRSA">
    <!-- Firma del DD con clave privada del CAF -->
  </FRMT>
</TED>
```

### 5.2 Proceso de Firma del TED

1. Construir el elemento `<DD>` con todos sus hijos
2. **Canonicalizar** (`C14N()`) el nodo DD
3. Convertir de UTF-8 a ISO-8859-1: `mb_convert_encoding($c14n, 'ISO-8859-1', 'UTF-8')`
4. Colapsar whitespace entre tags (regex)
5. Corregir entities (`&amp;` → `&amp;`, `&quot;` → `"`, etc. según contexto)
6. Firmar con la **clave privada del CAF** (RSASK): `openssl_sign($flattened, $sig, $rsask, OPENSSL_ALGO_SHA1)`
7. Base64-encode la firma y colocar en `FRMT`

---

## 6. Código de Autorización de Folios (CAF)

### 6.1 Qué es

El CAF es un archivo XML emitido por el SII que autoriza a una empresa a emitir documentos en un rango de folios específico. Contiene la clave privada RSA necesaria para firmar el TED.

### 6.2 Estructura del CAF

```xml
<?xml version="1.0" encoding="ISO-8859-1"?>
<AUTORIZACION>
  <CAF version="1.0">
    <DA>
      <RE>76123456-K</RE>          <!-- RUT emisor -->
      <RS>Empresa S.A.</RS>         <!-- Razón social -->
      <TD>39</TD>                   <!-- Tipo documento -->
      <RNG>
        <D>1</D>                    <!-- Desde -->
        <H>100</H>                  <!-- Hasta -->
      </RNG>
      <FA>2026-10-01</FA>          <!-- Fecha autorización -->
      <RSAPK>                       <!-- Clave pública RSA -->
        <M>...</M>                  <!-- Modulus -->
        <E>...</E>                  <!-- Exponent -->
      </RSAPK>
      <IDK>100</IDK>               <!-- 100=certificación, 300=producción -->
    </DA>
    <FRMA algoritmo="SHA1withRSA">
      <!-- Firma del SII sobre el DA -->
    </FRMA>
  </CAF>
  <RSASK>-----BEGIN RSA PRIVATE KEY-----
  ...
  -----END RSA PRIVATE KEY-----</RSASK>
  <RSAPUBK>-----BEGIN PUBLIC KEY-----
  ...
  -----END PUBLIC KEY-----</RSAPUBK>
</AUTORIZACION>
```

### 6.3 Reglas de Vigencia

- **Tipos 33, 43, 46, 56, 61**: folios expiran a **180 días** desde `FA` (fecha autorización)
- **Tipo 39 (boleta)**: sin vencimiento por tiempo
- **IDK**: debe coincidir con el ambiente (100=staging, 300=producción)
- **RUT**: debe coincidir con el RUT de la empresa emisora

### 6.4 Almacenamiento Seguro

El CAF contiene la **clave privada RSA** (`RSASK`) necesaria para firmar el TED. Debe almacenarse de forma segura:
- Fuera del directorio web público
- Con permisos restrictivos
- Idealmente cifrado en reposo

---

## 7. Firma Digital XMLDSIG

### 7.1 Algoritmos Requeridos

| Componente | Algoritmo | URI |
|---|---|---|
| Canonicalización | C14N | `http://www.w3.org/TR/2001/REC-xml-c14n-20010315` |
| Firma | RSA-SHA1 | `http://www.w3.org/2000/09/xmldsig#rsa-sha1` |
| Digest | SHA-1 | `http://www.w3.org/2000/09/xmldsig#sha1` |
| Transformación | Enveloped Signature | `http://www.w3.org/2000/09/xmldsig#enveloped-signature` |

### 7.2 Estructura de la Firma

```xml
<Signature xmlns="http://www.w3.org/2000/09/xmldsig#">
  <SignedInfo>
    <CanonicalizationMethod Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"/>
    <SignatureMethod Algorithm="http://www.w3.org/2000/09/xmldsig#rsa-sha1"/>
    <Reference URI="#DTE_T39F123">
      <Transforms>
        <Transform Algorithm="http://www.w3.org/2000/09/xmldsig#enveloped-signature"/>
      </Transforms>
      <DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1"/>
      <DigestValue><!-- base64(sha1(C14N(Documento))) --></DigestValue>
    </Reference>
  </SignedInfo>
  <SignatureValue>
    <!-- base64(openssl_sign(C14N(SignedInfo), pkey, SHA1)) -->
  </SignatureValue>
  <KeyInfo>
    <KeyValue>
      <RSAKeyValue>
        <Modulus><!-- del certificado --></Modulus>
        <Exponent>AQAB</Exponent>    <!-- 65537 en base64 -->
      </RSAKeyValue>
    </KeyValue>
    <X509Data>
      <X509Certificate><!-- certificado base64 --></X509Certificate>
    </X509Data>
  </KeyInfo>
</Signature>
```

### 7.3 Proceso de Firma

1. Computar DigestValue: `base64_encode(sha1(C14N($tag_element), true))`
2. Construir `<SignedInfo>` con CanonicalizationMethod, SignatureMethod, Reference
3. Computar SignatureValue: `openssl_sign(C14N($signedInfo), $sig, $privateKey, OPENSSL_ALGO_SHA1)`, luego `base64_encode($sig)`
4. Incluir `<KeyInfo>` con RSAKeyValue (Modulus, Exponent) y X509Certificate

### 7.4 Dos Firmas por Envío

Cada envío al SII requiere **dos firmas**:
1. **Firma del documento** (`<Documento>` o `<Exportaciones>`): firma individual de cada DTE
2. **Firma del sobre** (`<SetDTE>`): firma del conjunto de documentos

---

## 8. Autenticación con el SII

### 8.1 Flujo de Autenticación

```
1. getSemilla()
   SOAP: CrSeed.jws?WSDL → getSeed()
   → Retorna XML con <SII:RESP_BODY><SEMILLA>{semilla}</SEMILLA></SII:RESP_BODY>

2. Firmar semilla
   Construir XML: <getToken><item><Semilla>{semilla}</Semilla></item></getToken>
   Firmar con certificado digital

3. getToken()
   SOAP: GetTokenFromSeed.jws?WSDL → getToken(pszXml)
   → Retorna XML con <SII:RESP_BODY><TOKEN>{token}</TOKEN></SII:RESP_BODY>
   → ESTADO '00' = exitoso
```

### 8.2 Servidores SOAP

| Servicio | Producción | Certificación |
|---|---|---|
| WSDL base | `https://palena.sii.cl/DTEWS/` | `https://maullin.sii.cl/DTEWS/` |
| CrSeed | `{base}CrSeed.jws?WSDL` | `{base}CrSeed.jws?WSDL` |
| GetTokenFromSeed | `{base}GetTokenFromSeed.jws?WSDL` | `{base}GetTokenFromSeed.jws?WSDL` |
| QueryEstUp | `{base}QueryEstUp.jws?WSDL` | `{base}QueryEstUp.jws?WSDL` |
| QueryEstDte | `{base}QueryEstDte.jws?WSDL` | `{base}QueryEstDte.jws?WSDL` |
| QueryEstDteAv | `{base}services/QueryEstDteAv?WSDL` | `{base}services/QueryEstDteAv?WSDL` |
| RCV | `https://ws1.sii.cl/WSREGISTRORECLAMODTE/` | `https://ws2.sii.cl/WSREGISTRORECLAMODTECERT/` |

### 8.3 Token

- Validez: corta (se recomienda cachear ~60 minutos por seguridad)
- Se envía como Cookie: `TOKEN={token}` en las solicitudes HTTP

---

## 9. Envío de Documentos al SII

### 9.1 Envío de DTE (Facturas, Notas, Guías)

**Endpoint**: `https://{palena|maullin}.sii.cl/cgi_dte/UPL/DTEUpload`
**Método**: POST multipart/form-data

| Campo | Descripción |
|---|---|
| `rutSender` | RUT del certificado (sin DV) |
| `dvSender` | DV del certificado |
| `rutCompany` | RUT de la empresa (sin DV) |
| `dvCompany` | DV de la empresa |
| `archivo` | Archivo XML (multipart file upload) |

**Headers**:
```
User-Agent: Mozilla/5.0 (compatible; ...)
Cookie: TOKEN={token}
```

**Respuesta**: XML con `RECEPCIONDTE/STATUS` y `RECEPCIONDTE/TRACKID`

| Status | Significado |
|---|---|
| 0 | Envío OK |
| 1 | Sin permiso para enviar |
| 2 | Error de tamaño de archivo |
| 3 | Archivo truncado |
| 5 | No autenticado (token expirado) |
| 6 | Empresa no autorizada |
| 7 | Schema XML inválido |
| 8 | Error de firma |
| 9 | Problemas internos SII |
| 99 | XML ya recibido anteriormente |

### 9.2 Envío de Boletas

Las boletas electrónicas tienen un **mecanismo de envío separado** de los DTE regulares. Los endpoints específicos deben verificarse durante la implementación con el ambiente de certificación.

**Nota**: En LibreDTE Core, el `BoletaSenderStrategy` no está implementado (lanza excepción). El `SendXmlDocumentJob` solo maneja el endpoint DTEUpload para DTE regulares.

La investigación sugiere que boletas podrían usar endpoints REST distintos, pero esto requiere **verificación en ambiente de certificación**.

### 9.3 Límites

- EnvioBOLETA: máximo **500 boletas** por sobre
- EnvioDTE: máximo documentos según límite del SII

---

## 10. Consulta de Estado

### 10.1 Estado de Envío (por TrackID)

**SOAP**: `QueryEstUp.jws?WSDL` → `getEstUp(rut, dv, trackId, token)`

| Estado | Significado |
|---|---|
| EPR | Envío procesado |
| SOK | Schema OK, pendiente de procesamiento |
| RSC | Rechazado por schema |
| RPT | Rechazado parcial (algunos documentos) |
| RCT | Rechazado contenido |
| RFR | Rechazado por firma |

### 10.2 Estado de Documento Individual

**SOAP**: `QueryEstDte.jws?WSDL` / `QueryEstDteAv` → consulta por RUT emisor, tipo DTE, folio

---

## 11. Reporte de Consumo de Folios (RCOF / ConsumoFolios)

### 11.1 Propósito

Reporte diario al SII de los folios consumidos. Históricamente requerido para boletas electrónicas.

### 11.2 Estructura

```xml
<ConsumoFolios xmlns="http://www.sii.cl/SiiDte" version="1.0">
  <DocumentoConsumoFolios ID="LibreDTE_CONSUMO_FOLIO_{rut}_{fecha}_{ts}">
    <Caratula version="1.0">
      <RutEmisor>76123456-K</RutEmisor>
      <RutEnvia>11111111-1</RutEnvia>
      <FchResol>2024-01-15</FchResol>
      <NroResol>0</NroResol>
      <FchInicio>2026-10-27</FchInicio>
      <FchFinal>2026-10-27</FchFinal>
      <SecEnvio>1</SecEnvio>
      <TmstFirmaEnv>2026-10-27T23:59:00</TmstFirmaEnv>
    </Caratula>
    <Resumen>
      <TipoDocumento>39</TipoDocumento>
      <MntNeto>84034</MntNeto>
      <MntIva>15966</MntIva>
      <TasaIVA>19</TasaIVA>
      <MntTotal>100000</MntTotal>
      <FoliosEmitidos>10</FoliosEmitidos>
      <FoliosAnulados>0</FoliosAnulados>
      <FoliosUtilizados>10</FoliosUtilizados>
      <RangoUtilizados>
        <Inicial>1</Inicial>
        <Final>10</Final>
      </RangoUtilizados>
    </Resumen>
  </DocumentoConsumoFolios>
  <Signature>...</Signature>
</ConsumoFolios>
```

### 11.3 Estado Actual

Investigación sugiere que el RCOF **podría ya no ser requerido en producción** (boletas enviadas individualmente aparecen automáticamente en el RCV). Sin embargo, **ES requerido durante el proceso de certificación**. Se construirá la capacidad de generación para certificación y se verificará el requisito en producción.

---

## 12. Certificación / Habilitación

### 12.1 Proceso

Para que una empresa pueda emitir DTE, debe completar el proceso de certificación del SII:

1. **Solicitar set de prueba** al SII (ambiente certificación: maullin.sii.cl)
2. **Implementar** la emisión en ambiente de certificación
3. **Generar documentos de prueba** según el set requerido
4. **Enviar documentos** al SII de certificación
5. **Generar RCOF** para las boletas emitidas
6. **Enviar muestras impresas** (representación PDF)
7. **SII revisa y aprueba** (10-15 días hábiles)
8. **Obtener resolución** con fecha y número
9. **Solicitar folios de producción** (CAF con IDK=300)
10. **Comenzar emisión en producción** (palena.sii.cl)

### 12.2 Datos de Resolución

Tras la certificación, el SII emite una resolución con:
- **FchResol**: fecha de la resolución (ej: `2024-01-15`)
- **NroResol**: número de la resolución (ej: `80`; `0` durante certificación)

Estos datos se incluyen en la Carátula de cada envío.

---

## 13. Boletas Electrónicas — Diferencias Clave vs Facturas

| Aspecto | Boleta (39/41) | Factura (33/34) |
|---|---|---|
| Sobre XML | `EnvioBOLETA` (v1.1) | `EnvioDTE` (v1.0) |
| Precios | IVA incluido | Neto (sin IVA) |
| Receptor anónimo | Sí (66666666-6) | No (RUT obligatorio) |
| Giro receptor | No requerido | Requerido |
| Intercambio comercial | No | Sí (acuse recibo) |
| RCOF | Verificar requisito actual | No aplica |
| Máx por sobre | 500 | Sin límite explícito |
| Exigencia PDF | Simplificada | Completa |

---

## 14. Referencias Oficiales

### Documentos Técnicos SII (referenciados en código LibreDTE)

| Documento | URL |
|---|---|
| Envío Automático de DTE | `https://www.sii.cl/factura_electronica/factura_mercado/envio.pdf` |
| Autenticación Automática | `https://www.sii.cl/factura_electronica/factura_mercado/autenticacion.pdf` |
| Consulta Estado de Upload | `https://www.sii.cl/factura_electronica/factura_mercado/estado_envio.pdf` |
| Consulta Estado de DTE | `https://www.sii.cl/factura_electronica/factura_mercado/estado_dte.pdf` |
| Consulta Avanzada Estado DTE | `https://www.sii.cl/factura_electronica/factura_mercado/OIFE2006_QueryEstDteAv_MDE.pdf` |
| Reenvío Correo Validación | `https://www.sii.cl/factura_electronica/factura_mercado/OIFE2005_wsDTECorreo_MDE.pdf` |
| Instructivo Boleta Electrónica | `https://www.sii.cl/servicios_online/3532-instructivo_tecnico_be-3811.html` |

### Incertidumbres Pendientes

1. **Endpoints exactos para envío de boletas** — no verificados en código existente; requiere pruebas en ambiente de certificación
2. **Requisito actual del RCOF en producción** — verificar si sigue siendo obligatorio
3. **Versiones vigentes de schemas XSD** — verificar contra SII al iniciar implementación
4. **Cambios recientes en protocolo** — consultar actualizaciones en sitio del SII
