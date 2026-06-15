# DOLIBINANCE PARA DOLIBARR

![Captura de pantalla de DoliBinance](img/screenshot_dolibinance.png?raw=true "DoliBinance")

## Características

Este módulo conecta tu cuenta de Binance a tu Dolibarr.
Si aún no tienes una cuenta, sigue este enlace: [https://accounts.binance.com/register?ref=385030280](https://accounts.binance.com/register?ref=385030280).
Esto te permitirá agregar fácilmente el pago en Bitcoin y también aceptar cualquier otra criptomoneda aceptada por Binance.
Cuando un cliente desea pagar con criptomonedas, el módulo utiliza el promedio actual de la criptomoneda durante 24 horas para el pago de la factura, así como la dirección de recepción de tu billetera de Binance. El cliente luego ingresa la información de la transacción que ha realizado: identificación de transacción, dirección de envío, monto. Tan pronto como el depósito llega a tu cuenta de Binance, la factura se marca como "pagada".
Puedes verificar el saldo de tus activos de criptomonedas en tu billetera de Binance Spot directamente en Dolibarr sin necesidad de usar la aplicación de Binance.

## Próximamente

Las funciones de permisos de usuario aún no se han desarrollado en esta versión y llegarán más tarde. Todas las funciones están disponibles para todos los usuarios de Dolibarr, pero no se pueden realizar acciones importantes sin ser administrador.
Si tienes sugerencias relacionadas con la contabilidad mientras usas el módulo, no dudes en comunicárnoslas a través de [Olivier Andrade Sanchez](mailto:oandrade@aplose.fr?subject=[DoliBinanceRequest]-).

## Configuración

### Diccionario
Para ofrecer a tus clientes la opción de pago con criptomonedas, debes agregar la información necesaria al diccionario "Répertoire des adresses de paiement Binance" (Inicio->Configuración->Diccionario), que incluye las direcciones de recepción de criptomonedas que deseas aceptar como pago de tus facturas.
![Captura de pantalla del diccionario 1](img/doc-010-dictionnary.png?raw=true "Diccionario 1")
En este diccionario, agrega la información de tus diferentes direcciones de recepción creadas en Binance ([consulta la documentación de Binance para obtener más información al respecto](https://www.binance.com/fr/support/faq/comment-d%C3%A9poser-des-cryptos-sur-binance-115003764971)).
Por ejemplo, aquí se han ingresado tres direcciones (incluyendo dos para Bitcoin en dos redes diferentes).
![Captura de pantalla del diccionario 2](img/doc-011-dictionnary.png?raw=true "Diccionario 2")
Como puedes ver, puedes habilitar o deshabilitar criptomonedas sin eliminar la configuración.

### Página de configuración
El uso de este módulo requiere tener una cuenta en Binance.
¿Por qué Binance? Porque es el exchange de criptomonedas más grande del mundo. Sin embargo, ten en cuenta que no tienes acceso a las claves privadas y públicas de la billetera correspondiente, por lo que te recomendamos que utilices esta plataforma solo para enviar y recibir pagos o para hacer trading.
Si deseas asegurar tus criptomonedas, te recomendamos utilizar una billetera de hardware como una Ledger ([lee el artículo al respecto aquí](https://www.cryptocolo.fr/2023/02/08/not-your-keys-not-your-coins/)).
Para crear tu cuenta en Binance, no dudes en utilizar nuestro enlace de referidos de Binance: [https://accounts.binance.com/register?ref=385030280](https://accounts.binance.com/register?ref=385030280).
Las ventajas de ser referido por nosotros son numerosas (incluyendo el uso de un bot de trading específico, entre otros; contáctanos para obtener más información).
#### Configuración de las claves de la API
En primer lugar, crea tus claves de API en Binance siguiendo esta documentación (solo otorga permisos de lectura a estas claves): [Cómo crear claves de API en Binance](https://www.binance.com/fr/support/faq/comment-cr%C3%A9er-des-cl%C3%A9s-api-sur-binance).
Luego, ingresa tus claves de API en la página de configuración del módulo.
![Captura de pantalla de la configuración](img/doc-020-setup.png?raw=true "Configuración")
Puedes notar que el módulo te proporciona el precio actual de Bitcoin, la criptomoneda de referencia.

## Uso

### Facturación
¡Ahora tus facturas estándar pueden ser pagadas con criptomonedas!
Puedes utilizar el enlace de pago proporcionado por Dolibarr y que se muestra en tu factura:
![Captura de pantalla de uso 1](img/doc-030-use.png?raw=true "Uso 1")
Esta URL se puede integrar directamente en tus plantillas de correo electrónico utilizando la variable de sustitución: \__ONLINE_PAYMENT_TEXT_AND_URL__.

### Página de pago: paso 1
Siguiendo este enlace, tu cliente llega a la página de pago en línea de Dolibarr. En el primer paso, elige el par Cripto/Red de su elección de la lista que se ofrece:
![Captura de pantalla de uso 2](img/doc-040-use.png?raw=true "Uso 2")

### Página de pago: paso 2
En el segundo paso, se muestra el monto actual a pagar en la criptomoneda seleccionada.
Deberá ingresar la información de la transacción que ha realizado (el identificador de la transacción no es obligatorio y se obtendrá más tarde automáticamente en Binance).
![Captura de pantalla de uso 3](img/doc-050-use.png?raw=true "Uso 3")

### Página de pago: paso 3
En el último paso, se muestra el registro de su pago.
![Captura de pantalla de uso 4](img/doc-060-use.png?raw=true "Uso 4")

### Billetera de Binance
Puedes ver el estado de tus saldos de criptomonedas en tu billetera de Binance Spot utilizando el enlace "Portefeuille Binance" en el menú izquierdo:
![Captura de pantalla de uso 5](img/doc-070-use.png?raw=true "Uso 5")

### Historial de depósitos en tu billetera
Puedes ver el historial de depósitos realizados en las direcciones de depósito que has creado en Binance durante los últimos 90 días utilizando el enlace de menú "Historique des dépôts sur votre portefeuille".
El estado "1" indica una transacción validada (y, por lo tanto, se registra en el trabajo de DoliBinance):
![Captura de pantalla de uso 6](img/doc-080-use.png?raw=true "Uso 6")

### Transacciones
Los registros de la página de pago realizados por tus clientes se enumeran aquí. Un estado "1" indica que la transacción se ha completado, se ha recibido en Binance y la factura correspondiente se ha marcado como "Pagada":
![Captura de pantalla de uso 7](img/doc-090-use.png?raw=true "Uso 7")

### Trabajo de validación de transacciones (tareas automatizadas)
Las transacciones de blockchain pueden llevar varios minutos en ser validadas por la red correspondiente, por lo que no es posible hacer que tus clientes esperen en la página de pago.
Hemos elegido un procesamiento diferido de la transacción al validar regularmente los depósitos que llegan a tus direcciones de recepción.
El procesamiento se inicia cada minuto si tu configuración lo permite (asegúrate de que la crontab esté habilitada en tu servidor):
![Captura de pantalla de uso 8](img/doc-100-use.png?raw=true "Uso 8")
El trabajo se puede modificar como cualquier tarea automatizada en Dolibarr, y puedes editarlo para ejecutarlo solo una vez por hora, por ejemplo. También puedes optar por iniciar el proceso manualmente:
![Captura de pantalla de uso 9](img/doc-110-use.png?raw=true "Uso 9")
Una vez que la transacción se valida en la red y se ejecuta el trabajo, la factura correspondiente se marca automáticamente como "Pagada" y se agrega una nota privada para indicar la acción de DoliBinance:
![Captura de pantalla de uso 10](img/doc-120-use.png?raw=true "Uso 10")

## Mantente en contacto
Este módulo es una primera versión que evolucionará y mejorará según tus comentarios. No dudes en compartir tus ideas y comentarios con nosotros en [oandrade@aplose.fr](mailto:oandrade@aplose.fr).

## Traducciones

Las traducciones se pueden completar manualmente editando los archivos en los directorios *langs*.

## Licencias

### Código principal

GPLv3.

### Documentación

Todos los textos y readmes están bajo la licencia GFDL.
