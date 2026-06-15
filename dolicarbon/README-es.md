# DoliCarbon para [Dolibarr ERP & CRM](https://www.dolibarr.org)

**DoliCarbon** es un módulo de Dolibarr para elaborar y gestionar **inventarios de gases de efecto invernadero (GEI)** en su instancia: balances por ejercicio, líneas de actividad con factores de emisión, acciones de reducción, encuadre metodológico, cuadros de mando, informes y vínculos opcionales con los flujos de compra de Dolibarr.

Otros módulos externos están disponibles en [Dolistore](https://www.dolistore.com).

## Funcionalidades

### Inventarios (bilans)

- Crear un inventario por periodo de información; cada registro recibe una referencia automática **`CARBON-{año}-{secuencia}`** (véase la clase `DoliCarbonBilan`).
- Definir los límites del periodo (fechas inicio/fin), enlace opcional con un tercero, **totales** y **objetivos** en tCO2e, notas y estado de flujo de trabajo (borrador / validado / archivado).

### Líneas de actividad (asientos)

- Registrar emisiones por **alcance** (1, 2 o 3) y **categoría** (taxonomía del módulo), con cantidades, unidades y enlace a un **factor de emisión** cuando corresponda.
- Las líneas admiten flujo de validación, **comentarios**, trazabilidad y relación con objetos de origen (por ejemplo facturas de proveedor importadas).

### Factores de emisión

- Mantener una biblioteca de **factores de emisión** (con campos de versionado en el modelo de datos) y activar o desactivar factores según el esquema.

### Acciones de reducción

- Asociar **acciones** a un inventario: ahorros estimados, costes, puntuaciones y estado para seguir el plan de reducción.

### Encuadre metodológico (« cadrage »)

- Documentar el alcance, exclusiones, materialidad, años de referencia e información, y notas en un objeto dedicado usado en la información.

### Cuadro de mando

- Ver indicadores agregados: totales por alcance, serie temporal y principales categorías a partir de los datos del inventario.

### Informes

- Pantalla **Informe** de la aplicación integrada: resumen ejecutivo, desglose analista, anexo metodológico, texto de **advertencia de comunicación** opcional, rango de **incertidumbre**, exportación **CSV** o **JSON** e **instantánea** (snapshot) para un punto reproducible en el tiempo.

### Calidad de datos y colaboración

- Pantalla **Calidad** para pasos del flujo de trabajo, **comentarios** y trazabilidad orientada a auditoría en las líneas.

### Plan de transición

- Pantalla **Transición** para comparar acciones de reducción (resumen tipo coste/beneficio según la interfaz).

### Importación desde Dolibarr

- Ejecutar el asistente **Importación** para incorporar datos de Dolibarr a las líneas del inventario (véase `carbon_import.php` y los servicios AJAX relacionados).

### Disparadores opcionales (administración)

En **Inicio → Configuración → Módulos → DoliCarbon → Configuración** hay dos opciones (`DOLICARBON_TRIGGER_NOTIFY` y `DOLICARBON_AUTO_IMPORT_SUPPLIER_INVOICE`):

1. **Mensajes al validar**  
   Si está activada, al validar una **factura de proveedor**, una **nota de gastos** o un **albarán** pueden mostrarse mensajes de Dolibarr hacia DoliCarbon (con enlace a la importación para facturas de proveedor).

2. **Línea automática desde factura de proveedor**  
   Si esta opción y los mensajes están activos, al validar una **factura de proveedor** puede crearse automáticamente una línea de **alcance 3** en la categoría **`purchases_services`**, usando el **total sin impuestos en EUR**, **solo si** existe un inventario en **borrador** y un **factor de emisión activo** adecuado (mismo alcance y categoría). Se evitan duplicados mediante un hash de importación.

Si las opciones están desactivadas, no se generan mensajes ni líneas automáticas.

## Interfaz de usuario

- **Aplicación web integrada** (menú **DoliCarbon** → `custom/dolicarbon/index.php`): panel, bilans, cadrage, líneas, factores, acciones, calidad, transición, importación, informe. Se requiere permiso de **lectura** del módulo.
- Las **pantallas PHP clásicas** siguen disponibles (por ejemplo `carbon_bilan_list.php`, `carbon_factors.php`, otras `carbon_*.php`).

El bundle Angular incluye cadenas de interfaz en **francés** (`assets/i18n/fr.json`). Las traducciones del módulo Dolibarr están en **francés** (`fr_FR`) e **inglés** (`en_US`) bajo `langs/`.

## Permisos

Cuatro derechos: **leer**, **crear/modificar**, **eliminar** y **validar** (validación / bloqueo del flujo del inventario). Asignación en **Usuarios y grupos**.

## Requisitos previos

- **Dolibarr 17** o versión compatible posterior según el descriptor del módulo (`need_dolibarr_version`).
- **PHP 8.1** como mínimo (`phpmin`).

No se declara otra dependencia obligatoria de módulo; las funciones opcionales suponen que existan los objetos Dolibarr usados (p. ej. facturas de proveedor).

## Instalación

Requisito: una instalación operativa de Dolibarr. Descarga en [dolibarr.org](https://www.dolibarr.org). También hay ofertas alojadas (véase más abajo).

### Desde un ZIP

Si el módulo se distribuye como `module_dolicarbon-x.y.z.zip` (por ejemplo desde [Dolistore](https://www.dolistore.com)), use **Inicio → Configuración → Módulos → Desplegar/instalar módulo externo** y suba el archivo.

### Pasos finales

1. Iniciar sesión como administrador.
2. Abrir **Configuración → Módulos**, activar **DoliCarbon**.
3. Abrir **Configuración → Módulos → DoliCarbon → Configuración** para las dos opciones de disparadores si hace falta.

## Configuración

1. **Configuración del módulo**: activar o desactivar mensajes e importación automática como se describe arriba.
2. **Permisos**: conceder al menos **leer** para abrir la aplicación; **escribir**, **eliminar** y **validar** según los perfiles.

## Dolibarr en la nube (Ma Gestion Cloud)

Puede ejecutar Dolibarr con los módulos Aplose en **[Ma Gestion Cloud](https://www.ma-gestion-cloud.fr)**: alojamiento, copias de seguridad y soporte. Registro / prueba (seguimiento visitantes Dolistore):

**[Crear una cuenta — Ma Gestion Cloud](https://aplose.ma-gestion-cloud.fr/custom/sellyoursaas/myaccount/register.php?origin=dolistore)**

Contacto: [contact@aplose.fr](mailto:contact@aplose.fr)

Los planes comerciales y módulos incluidos dependen de su suscripción; consulte a Ma Gestion Cloud.

## Asistencia

- Correo: [contact@aplose.fr](mailto:contact@aplose.fr)
- Editor: [Aplose](https://www.aplose.fr)

## Licencias

### Código principal

GPLv3 o (a su elección) cualquier versión posterior. Véase el archivo `COPYING`.

### Documentación

Esta documentación está bajo [GFDL](https://www.gnu.org/licenses/fdl-1.3.en.html).

## Versión

Versión actual del módulo: **1.0.0** (véase `core/modules/modDoliCarbon.class.php`).
