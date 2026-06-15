# DOLILINKS PARA [DOLIBARR ERP & CRM](https://www.dolibarr.org)

## Descripción del módulo

DoliLinks es un módulo para Dolibarr que permite crear y gestionar enlaces jerárquicos entre empresas (terceros). Ofrece una visualización clara de las relaciones padre-hijo entre empresas y facilita la gestión de estructuras organizacionales complejas.

## Funcionalidades principales

### 1. Gestión de enlaces entre empresas

El módulo permite crear relaciones jerárquicas entre empresas:
- **Enlaces padre-hijo**: Definir qué empresas son padres o hijos de otras empresas
- **Tipos de enlaces personalizables**: Crear tipos de enlaces específicos (filial, sucursal, socio, etc.)
- **Prevención de enlaces circulares**: El sistema impide vincular una empresa consigo misma

![Interfaz de gestión de enlaces entre empresas: añadir un padre](img/screenshot_dolilinks_links02.png)
![Interfaz de gestión de enlaces entre empresas](img/screenshot_dolilinks_links03.png)


### 2. Visualización jerárquica

#### 2.1 Visualización en la ficha de empresa
Los enlaces se muestran automáticamente en la ficha de cada empresa:
- **Sección Padres**: Lista de empresas padre con enlaces directos
- **Sección Hijos**: Lista de empresas hijas con enlaces directos
- **Botones de acción**: Adición rápida de nuevos enlaces y acceso al diagrama

![Visualización de enlaces en la ficha de empresa](img/screenshot_dolilinks_links01.png)

#### 2.2 Diagrama interactivo
Visualización gráfica completa de las relaciones:
- **Red jerárquica**: Visualización de todos los padres, hijos y nietos
- **Navegación interactiva**: Clic en los nodos para acceder a las fichas de empresa
- **Leyenda de colores**: Distinción visual entre padres (gris), empresa actual (verde) e hijos (azul)
- **Tipos de enlaces**: Visualización de las etiquetas de tipos de enlaces en las conexiones

![Diagrama interactivo de relaciones](img/screenshot_diagram_interactive.png)

### 3. Gestión de tipos de enlaces

#### 3.1 Configuración de tipos
- **Creación de tipos personalizados**: Definir tipos de relaciones específicos para su organización
- **Gestión centralizada**: Interfaz de administración para crear y modificar tipos
- **Diccionario integrado**: Los tipos se almacenan en el diccionario de Dolibarr

![Interfaz de gestión de tipos de enlaces: acceso al diccionario](img/screenshot_link_types_management01.png)
![Interfaz de gestión de tipos de enlaces: añadir tipos de enlaces](img/screenshot_link_types_management02.png)

### 4. Integración con el ecosistema Dolibarr

#### 4.1 Hooks y extensiones
- **Integración nativa**: El módulo se integra perfectamente en la interfaz de Dolibarr
- **Hooks personalizados**: Extensión de funcionalidades a través del sistema de hooks

#### 4.2 Filtrado de contactos de facturación
- **Filtrado inteligente**: Opción para ofrecer solo contactos de facturación al enviar emails (¡no envíe facturas a los clientes de sus clientes!!!)
- **Contactos de terceros hijos**: Visualización de contactos de empresas vinculadas en las fichas de contacto
- **Configuración flexible**: Activación/desactivación a través de los parámetros del módulo

![Filtrado de contactos hijos en una orden](img/screenshot_contact_filtering_order.png)
![Filtrado de contactos de facturación en envío de email](img/screenshot_contact_filtering_invoice_email.png)

#### 4.3 Compatibilidad
- **Multi-entidad**: Soporte completo del modo multi-entidad de Dolibarr
- **Seguridad**: Respeto de los derechos de acceso y la seguridad de Dolibarr
- **Traducciones**: Soporte multilingüe (francés, inglés, alemán, español)

### 5. Funcionalidades avanzadas

#### 5.1 Importación de datos
- **Migración desde SocParent**: Herramienta de importación para migrar datos del módulo SocParent

![Interfaz de importación de datos](img/screenshot_admin01.png)

#### 5.2 Informes y estadísticas
- **Contadores automáticos**: Visualización del número de padres/hijos para cada empresa
- **Navegación facilitada**: Enlaces directos a las fichas de empresas vinculadas
- **Vista general**: Acceso rápido al diagrama completo de relaciones

## Instalación

### Prerrequisitos
- Dolibarr ERP & CRM instalado
- Derechos de administrador para la instalación del módulo

### Instalación vía interfaz Dolibarr
1. Descargue el módulo desde [Dolistore.com](https://www.dolistore.com)
2. Conéctese a Dolibarr como administrador
3. Vaya a `Inicio > Configuración > Módulos > Desplegar módulo externo`
4. Suba el archivo ZIP del módulo
5. Active el módulo en la lista de módulos disponibles

### Configuración inicial
1. Acceda a `Configuración > Módulos > DoliLinks`
2. Configure los parámetros según sus necesidades
3. Cree sus tipos de enlaces personalizados si es necesario

## Uso

### Crear un enlace entre empresas
1. Abra la ficha de la empresa concernida
2. En la sección "Padres" o "Hijos", haga clic en el botón "+"
3. Seleccione la empresa a vincular en la lista desplegable
4. Elija el tipo de enlace (opcional)
5. Haga clic en "Añadir"

### Visualizar las relaciones
1. Desde la ficha de empresa, haga clic en "Ver diagrama"
2. El diagrama interactivo se muestra con todas las relaciones
3. Haga clic en cualquier nodo para acceder a la ficha de la empresa

### Gestionar los tipos de enlaces
1. Vaya a `Configuración > Diccionarios > Tipo de enlace entre empresas`
2. Cree, modifique o elimine los tipos según sus necesidades

![Diccionario tipo de enlace](img/screenshot_link_types_management01.png)
![Diccionario tipo de enlace: gestión](img/screenshot_link_types_management02.png)


## Configuración

### Parámetros disponibles
- **Filtrado de contactos**: Opción para ofrecer solo contactos de facturación al enviar emails

### Personalización
El módulo puede extenderse vía:
- Hooks personalizados
- Plantillas modificables
- Clases PHP extensibles

## Soporte y desarrollo

### Licencia
- **Código principal**: GPLv3 o versión posterior
- **Documentación**: GFDL

### Soporte
- Documentación completa en el módulo
- Compatible con versiones recientes de Dolibarr

### Desarrollo
El módulo está desarrollado siguiendo los estándares de Dolibarr:
- Arquitectura MVC
- Sistema de hooks
- Gestión de traducciones
- Seguridad integrada
