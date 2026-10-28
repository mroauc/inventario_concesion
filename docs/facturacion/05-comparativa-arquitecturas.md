# Comparativa de Arquitecturas

Fecha: 2026-10-27
Objetivo: Evaluar tres alternativas para integrar facturación electrónica en ROAVAL

---

## 1. Las Tres Alternativas

| | A: Motor Propio | B: LibreDTE Core (self-hosted) | C: API Comercial (ej. LibreDTE API, Haulmer, Acepta) |
|---|---|---|---|
| **Descripción** | Motor DTE desarrollado internamente con DOMDocument + openssl + SOAP/cURL, integrado nativamente en Laravel 8 | Integrar `libredte/libredte-lib-core` como dependencia Composer, delegando la generación y firma de documentos | Suscripción a servicio externo que expone API REST para emisión de DTE, abstrayendo toda la complejidad SII |

---

## 2. Evaluación Detallada

### 2.1 Compatibilidad Técnica

| Criterio | A: Motor Propio | B: LibreDTE Core | C: API Comercial |
|---|---|---|---|
| PHP 8.2.26 compatible | ✅ Sí | ❌ **Requiere PHP 8.5+** | ✅ Solo HTTP client |
| Laravel 8 compatible | ✅ Nativo | ❌ Ecosistema Symfony DI | ✅ Independiente |
| Extensiones PHP requeridas | openssl, soap, curl, mbstring, dom (todos instalados en MAMP) | Igual + dependencias derafu/* | Solo curl/json |
| Dependencias adicionales | Ninguna nueva | 12+ paquetes derafu/* (dev-main) | Guzzle (ya instalado) |

**Veredicto**: B es **técnicamente inviable** sin upgrade de PHP. A y C son viables.

### 2.2 Cobertura Funcional

| Funcionalidad | A: Motor Propio | B: LibreDTE Core | C: API Comercial |
|---|---|---|---|
| Boleta 39 (afecta) | ✅ Construir | ❌ Envío NO implementado | ✅ Incluido |
| Boleta 41 (exenta) | ✅ Construir | ❌ Envío NO implementado | ✅ Incluido |
| Factura 33 | ✅ Construir (Fase F) | ⚠️ Build sí, envío no | ✅ Incluido |
| Factura exenta 34 | ✅ Construir (Fase F) | ⚠️ Build sí, envío no | ✅ Incluido |
| Nota crédito 61 | ✅ Construir (Fase G) | ⚠️ Build sí, envío no | ✅ Incluido |
| TED / Timbre | ✅ Construir | ✅ Implementado | ✅ Incluido |
| Firma XMLDSIG | ✅ Construir | ✅ Via derafu/signature | ✅ Incluido |
| RCOF | ✅ Construir | ✅ Implementado | ✅ Incluido |
| Envío al SII | ✅ Construir | ❌ Solo DTE, NO boletas | ✅ Incluido |
| Consulta estado | ✅ Construir | ✅ Implementado | ✅ Incluido |

**Veredicto**: B no cubre boletas (prioridad #1). A requiere construir todo. C incluye todo.

### 2.3 Licenciamiento

| Aspecto | A: Motor Propio | B: LibreDTE Core | C: API Comercial |
|---|---|---|---|
| Licencia | Propiedad ROAVAL | **AGPL-3.0+** | Comercial (SaaS) |
| Efecto sobre ROAVAL | Ninguno | **ROAVAL debería ser AGPL** | Ninguno |
| Código fuente | Privado | Obligado a publicar | Privado |
| Redistribución | Libre | Bajo AGPL | Bajo contrato |
| Términos adicionales | N/A | Atribución obligatoria al autor | Según proveedor |

**Veredicto**: B tiene **riesgo legal severo** para un proyecto comercial cerrado. A y C no tienen restricciones.

### 2.4 Costos

| Concepto | A: Motor Propio | B: LibreDTE Core | C: API Comercial |
|---|---|---|---|
| Costo de desarrollo | Alto (4-6 semanas dev) | Inviable | Bajo (1-2 semanas) |
| Costo mensual | $0 | $0 | ~$40.000-80.000 CLP + IVA /empresa |
| Costo por documento | $0 | $0 | $0-50 CLP /documento (según plan) |
| Costo 5 empresas / 12 meses | $0 | N/A | ~$2.400.000 - $4.800.000 CLP |
| Costo 10 empresas / 12 meses | $0 | N/A | ~$4.800.000 - $9.600.000 CLP |

**Veredicto**: A tiene costo cero a largo plazo. C escala linealmente con cada empresa.

### 2.5 Control y Dependencia

| Aspecto | A: Motor Propio | B: LibreDTE Core | C: API Comercial |
|---|---|---|---|
| Control del código | ✅ Total | ⚠️ Dependencia derafu/* | ❌ Caja negra |
| Independencia operativa | ✅ Total | ⚠️ Upstream define API | ❌ Depende del proveedor |
| Personalización | ✅ Sin límites | ⚠️ Limitada por arquitectura | ❌ Según API del proveedor |
| Disponibilidad | Tu servidor | Tu servidor | **Servidor del proveedor** |
| Migración | N/A | Compleja | Compleja (vendor lock-in) |
| Soporte SII directo | ✅ Tú manejas | ✅ Tú manejas | ❌ Via proveedor |
| Multi-tenancy nativo | ✅ Diseñado desde inicio | ⚠️ Requiere adapter | ⚠️ Suscripción por empresa |

**Veredicto**: A ofrece independencia total. C introduce dependencia de disponibilidad y funcionalidad del proveedor.

### 2.6 Complejidad y Riesgo

| Aspecto | A: Motor Propio | B: LibreDTE Core | C: API Comercial |
|---|---|---|---|
| Complejidad de implementación | ❌ Alta | ❌ Inviable | ✅ Baja |
| Riesgo técnico | ⚠️ Medio (firma XML, certificación) | ❌ Alto (PHP, AGPL, deps) | ✅ Bajo |
| Certificación SII | ⚠️ 10-15 días hábiles (por empresa) | N/A | ✅ Asistida por proveedor |
| Tiempo hasta producción | ⚠️ 8-10 semanas | ❌ Inviable | ✅ 2-4 semanas |
| Mantenimiento continuo | ⚠️ Propio (cambios SII) | ❌ N/A | ✅ Incluido en suscripción |
| Expertise requerido | ⚠️ XML, criptografía, SII | ❌ N/A | ✅ Solo REST API |

**Veredicto**: C es más rápido y simple. A requiere inversión pero es factible (probado por RedAndes).

---

## 3. Matriz de Decisión

Ponderación basada en los objetivos del proyecto ROAVAL (multi-empresa, largo plazo, control).

| Criterio | Peso | A: Motor Propio | B: LibreDTE Core | C: API Comercial |
|---|---|---|---|---|
| Compatibilidad PHP 8.2 | ELIMINATORIO | ✅ Pasa | ❌ **ELIMINADO** | ✅ Pasa |
| Soporte boleta 39 | ELIMINATORIO | ✅ Construir | ❌ **ELIMINADO** | ✅ Incluido |
| Licencia compatible | ELIMINATORIO | ✅ Pasa | ❌ **ELIMINADO** | ✅ Pasa |
| Costo a largo plazo (10 empresas) | 25% | ⭐⭐⭐⭐⭐ $0 | — | ⭐⭐ $4.8-9.6M CLP/año |
| Control y personalización | 20% | ⭐⭐⭐⭐⭐ Total | — | ⭐⭐ API fija |
| Multi-tenancy nativo | 15% | ⭐⭐⭐⭐⭐ Diseñado | — | ⭐⭐⭐ Suscripción por empresa |
| Tiempo hasta producción | 15% | ⭐⭐⭐ 8-10 sem | — | ⭐⭐⭐⭐⭐ 2-4 sem |
| Independencia operativa | 15% | ⭐⭐⭐⭐⭐ Total | — | ⭐⭐ Vendor lock-in |
| Mantenimiento | 10% | ⭐⭐⭐ Propio | — | ⭐⭐⭐⭐ Incluido |

**Puntuación ponderada** (sin B, que fue eliminado):
- **A: Motor Propio**: 4.40 / 5.00
- **C: API Comercial**: 2.90 / 5.00

---

## 4. Recomendación

### Opción Recomendada: A — Motor Propio

**Razones**:

1. **LibreDTE Core es inviable** — PHP 8.5+, AGPL, boleta sending no implementado. Eliminado por criterios técnicos y legales.

2. **La factibilidad está probada** — RedAndes demuestra que una integración SII completa funciona con DOMDocument + openssl + SoapClient + cURL, todos disponibles en PHP 8.2 / MAMP.

3. **Costo marginal cero** — cada nueva empresa es una fila en `configuracion_fiscal` + sus certificados y CAF. Sin suscripciones mensuales ni costos por documento.

4. **Multi-tenancy nativo** — diseñado desde el inicio con el patrón `id_concession` existente en ROAVAL, en lugar de adaptar una API externa.

5. **Control total** — cualquier cambio del SII se puede adaptar inmediatamente sin esperar a un proveedor.

### Alternativa de Contingencia: C — API Comercial

Si durante la implementación de la Opción A surgen dificultades insuperables (especialmente en la certificación SII), se puede pivotar a una API comercial con inversión mínima perdida:

- Los modelos y migraciones (Fase A) son reutilizables — solo cambia la capa de comunicación
- Los servicios de cálculo tributario son reutilizables
- La UI (Fase D) es completamente reutilizable
- Solo se descartaría la capa de comunicación SII (Fase C)

**Punto de decisión**: Al finalizar la Fase B (servicios core), evaluar si la generación de XML y firma funcionan correctamente contra los XSD. Si hay problemas fundamentales, considerar el pivot antes de invertir en Fase C.

---

## 5. Riesgos Específicos de la Opción A y Mitigaciones

| Riesgo | Probabilidad | Severidad | Mitigación |
|---|---|---|---|
| Firma XML rechazada por SII | Baja | Alta | Validar contra XSD antes de enviar. C14N() estándar. Tests con maullin.sii.cl. Fallback saveHTML() si C14N falla. |
| Certificación SII demora >15 días | Media | Media | Iniciar con ambiente staging temprano. RCOF listo desde Fase C. |
| Cambio de protocolo SII | Baja | Media | Monitorear sitio SII. Modularidad permite cambiar solo el cliente afectado. |
| Endpoints de boleta distintos a DTE | Media | Media | Verificar en Fase C con ambiente certificación. Mantener opción API como fallback. |
| Complejidad subestimada | Media | Media | Punto de decisión al final de Fase B. Opción C como fallback definido. |

---

## 6. Resumen Ejecutivo

```
┌─────────────────────────────────────────────────────────────┐
│                    DECISIÓN DE ARQUITECTURA                  │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  ✅ RECOMENDADO:  Opción A — Motor Propio                   │
│                                                              │
│  • Único viable técnicamente (PHP 8.2, sin AGPL)            │
│  • Costo: $0 marginal por empresa/documento                 │
│  • Multi-tenancy nativo con id_concession                   │
│  • Factibilidad probada por RedAndes                        │
│  • Inversión: 8-10 semanas de desarrollo                    │
│                                                              │
│  ❌ ELIMINADO:    Opción B — LibreDTE Core                  │
│  • Requiere PHP 8.5+ (incompatible)                         │
│  • AGPL-3.0+ (incompatible con proyecto cerrado)            │
│  • Boleta sending no implementado                           │
│                                                              │
│  🔄 CONTINGENCIA: Opción C — API Comercial                  │
│  • Fallback si Opción A falla en certificación              │
│  • Punto de decisión: fin de Fase B                         │
│  • Costo: ~$40-80K CLP/mes por empresa                     │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```
