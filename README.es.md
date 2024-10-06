# [WIP] <img src="logo.png" width="32" height="32" alt="Module logo"> Módulo DNI Cliente PrestaShop

## Versión 0.9.0

Por defecto, PrestaShop no permite configurar el DNI (Documento Nacional de Identidad) del cliente al crear una cuenta.
En cambio, el campo se guarda a nivel de dirección, lo que en algunos casos no es lo más adecuado. Tampoco existe una validación adicional para el campo DNI;
un cliente puede tener varias direcciones con diferentes DNI, e incluso se puede utilizar el mismo DNI para varias direcciones de distintos clientes.
Este módulo añade un nuevo campo al formulario de registro y edición del cliente, cambiando el manejo del campo DNI a nivel de información personal del cliente.
El módulo también proporciona opciones de validación adicionales para el campo DNI, como hacerlo obligatorio, único y validarlo contra una expresión regular.

## Características

- Añade un nuevo campo al formulario de registro y edición del cliente, permitiéndole guardar su DNI.
- Muestra el campo DNI en el back office también, para que los usuarios del back office puedan verlo y editarlo fácilmente. También muestra el DNI en el listado de clientes.
- Permite configurar el DNI como requerido, único y validarlo contra una expresión regular usando la página de configuración del módulo.
- Opción de sobreescribir el campo DNI de las direcciones cuando el DNI de un cliente cambia, haciendo que se copie ese valor a todas las direcciones de ese cliente. Útil para módulos que utilizan el
  campo de dirección DNI.
- Se pueden agregar validaciones adicionales añadiendo una clase de validación personalizada dentro de la carpeta `custom_validators` del módulo. El módulo incluye un validador personalizado que
  verifica si el DNI es un RUT chileno válido.
- El DNI se almacena en una nueva tabla de la base de datos, vinculada al ID del cliente, por lo que se puede recuperar fácilmente y utilizar en otros módulos o personalizaciones.
- Proporciona hooks personalizados para añadir funcionalidades adicionales programáticamente.
- El módulo es totalmente traducible.

## Requisitos

- Probado en PrestaShop 8.1, pero debería funcionar en cualquier versión de PrestaShop 1.7.7 o superior.
- PHP 8.0 o superior.
- Composer, para generar los archivos de autoload necesarios.

## Instalación (desde este repositorio)

1. Descarga este repositorio a una carpeta llamada `customer_dni`.
2. Entra en dicha carpeta, y ejecuta el siguiente comando para generar los archivos de Composer necesarios:

```bash
composer dump-autoload -o --no-dev
```

3. Comprime la carpeta en un archivo ZIP.
4. Sube el archivo ZIP a tu instancia de PrestaShop. Puedes hacer esto yendo al back office de tu tienda PrestaShop y navegando a la sección `Módulos`.
   Haz clic en el botón `Subir un módulo` y selecciona el archivo ZIP que acabas de crear. También puedes subir el archivo ZIP directamente a la carpeta `modules` de tu instalación de PrestaShop.
   Si eliges este método, asegúrate de extraer el archivo ZIP después de subirlo, para que se cree la carpeta `customer_dni` dentro de la carpeta `modules`.
5. Después de subir el archivo ZIP, el módulo debería aparecer en la lista de módulos en el back office, donde puedes instalarlo.
6. Una vez instalado el módulo, haz clic en el botón `Configurar` para acceder a la página de configuración del módulo.
7. Configura el módulo según tus necesidades y guarda los cambios.
8. El módulo está ahora listo para usarse. El campo DNI debería mostrarse en el formulario de registro y edición del cliente.

## Configuración

El módulo tiene una página de configuración donde puedes establecer las siguientes opciones:

- **Mostrar DNI del cliente en el back-office**: Muestra el campo DNI en el listado de clientes del back office, lo que también permite filtrar y ordenar por el valor del DNI.
- **Marcar como requerido**: Hace que el campo DNI sea obligatorio en el formulario de registro y edición del cliente.
- **Marcar como único**: Hace que el campo DNI sea único, por lo que el mismo DNI no puede ser utilizado por varios clientes.
- **Sobreescribir campo DNI de dirección**: Sobreescribe el campo DNI por defecto en el formulario de dirección, para que cuando el cliente guarde el DNI en el formulario de cliente,
  se guarde también en el campo DNI de la dirección para **todas** las direcciones relacionadas con ese cliente.
  Ten en cuenta que el campo DNI por defecto de la dirección tiene una longitud máxima de 16 caracteres, por lo que si el DNI del cliente es más largo que eso, se truncará.
- **Usar expresión regular para validar**: Puedes establecer una expresión regular a utilizar para validar el DNI.
- **Usar validadores personalizados**: Habilita el uso de validadores personalizados para el campo DNI.
  Puedes subir una clase de validación personalizada dentro de la carpeta `custom_validators` del módulo para añadir validaciones adicionales al campo DNI.

## Uso

Una vez instalado y configurado el módulo, el campo DNI debería mostrarse en el formulario de registro y edición del cliente. El campo DNI también se muestra en el back office, por lo que los usuarios
con permisos de edición de clientes pueden editarlo fácilmente.

El campo DNI se almacena en una nueva tabla en la base de datos, vinculada al ID del cliente.
Al restablecer o desinstalar el módulo, el campo DNI se eliminará del formulario de cliente, pero los datos de DNI guardados previamente permanecerán en la tabla `customer_dni` del módulo.

Puedes utilizar el campo DNI en otros módulos o personalizaciones recuperándolo directamente de la tabla de base de datos `customer_dni` utilizando el ID del cliente.

## Personalización

Puedes personalizar el módulo añadiendo validadores adicionales para el campo DNI.
Para hacerlo, añade una clase de validador personalizado que implemente la interfaz `CustomValidator` a la carpeta `custom_validators`.
El módulo tiene un validador personalizado integrado que comprueba el DNI contra el formato de RUT chileno.
Puedes utilizar esta clase como referencia para crear tu propio validador personalizado.

El módulo también proporciona dos hooks personalizados que pueden ser utilizados para añadir funcionalidades programáticamente:

- `actionCustomerDNIAddAfter`: Se ejecuta después de que el DNI del cliente se guarda en la base de datos, ya sea un DNI nuevo o una actualización de uno existente.
- `actionCustomerDNIDeleteAfter`: Se ejecuta cuando el DNI del cliente se elimina de la base de datos, lo que suele ocurrir cuando se elimina el cliente.
  Este hook se ejecutará incluso si no había un DNI asociado al cliente en el momento de la eliminación.

Ambos hooks devuelven el ID del cliente y el valor del DNI como parámetros.

## Licencia

Este módulo está licenciado bajo la Licencia MIT. Puedes ver los detalles de la licencia en el archivo [LICENSE](LICENSE).

## TODOs

Ideas para futuras mejoras no contempladas en la versión actual del módulo:

- Añadir soporte para PrestaShop 1.7.6 y versiones anteriores.
- Permitir seleccionar la ubicación del campo DNI en el formulario de cliente del front office (por ejemplo, antes o después del campo de email).
- Validar el DNI durante la creación de un cliente mediante la API.
- Ofrecer hooks que permitan añadir validaciones personalizadas al DNI de un cliente programáticamente.