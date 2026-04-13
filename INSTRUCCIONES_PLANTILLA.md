# Plantilla para Importar Productos

## Estructura del archivo

El archivo debe tener exactamente 5 columnas en este orden:

1. **codigo** - Código único del producto
2. **nombre** - Nombre del producto
3. **stock** - Stock del producto en la bodega seleccionada
4. **ubicaciones** - Posiciones separadas por guiones (ej: A1-B2-C3)
5. **categoria** - Nombre de la categoría (debe existir previamente)

## Formatos soportados
- Excel (.xlsx)
- CSV (.csv) 
- Texto (.txt)

## Notas importantes
- La primera fila debe contener los nombres de las columnas
- Las ubicaciones se separan con guiones (-) sin espacios
- La categoría debe existir previamente en el sistema
- Si el código ya existe, se actualizará el stock en la bodega seleccionada
- Si no existe, se creará un nuevo producto
- El stock se asigna específicamente a la bodega seleccionada al importar

## Ejemplo
```
codigo,nombre,stock,ubicaciones,categoria
PROD001,Laptop Dell,10,A1-B2,Electronica
PROD002,Mouse Inalambrico,50,C3,Accesorios
```