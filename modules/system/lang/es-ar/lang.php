<?php

return [
    'app' => [
        'name' => 'October CMS',
        'tagline' => 'Getting back to basics',
    ],
    'directory' => [
        'create_fail' => "No se puede crear el directorio: :name",
    ],
    'file' => [
        'create_fail' => "No se puede crear el archivo: :name",
    ],
    'combiner' => [
        'not_found' => "El archivo combinado ':name' no se encuentra.",
    ],
    'system' => [
        'name' => 'Sistema',
        'menu_label' => 'Sistema',
        'categories' => [
        'cms' => 'CMS',
        'misc' => 'Miscelánea',
        'logs' => 'Registros',
        'mail' => 'Correo',
        'shop' => 'Comprar',
        'team' => 'Equipo',
        'users' => 'Usuarios',
        'system' => 'Sistema',
        'social' => 'Social',
        'events' => 'Eventos',
        'customers' => 'Clientes',
        'my_settings' => 'Mis configuraciones',
        ],
    ],
    'plugin' => [
        'unnamed' => 'Plugin sin nombre',
        'name' => [
            'label' => 'Nombre del plugin',
            'help' => 'Buscar el plugin por su nombre de codigo unico. Por ejemplo: RainLab.Blog',
        ],
    ],
    'plugins' => [
        'manage' => 'Administrar plugins',
        'enable_or_disable' => 'Activar o desactivar',
        'enable_or_disable_title' => 'Activar o desactivar plugins',
        'remove' => 'Eliminar',
        'refresh' => 'Actualizar',
        'disabled_label' => 'Desactivado',
        'disabled_help' => 'Los Plugins desactivados son ignorados por la aplicación.',
        'selected_amount' => 'Plugins seleccionados: :amount',
        'remove_confirm' => '¿Está usted seguro?',
        'remove_success' => "Se eliminaron exitosamente los plugins del sistema.",
        'refresh_confirm' => '¿Está usted seguro?',
        'refresh_success' => "Se actualizaron exitosamente los plugins del sistema.",
        'disable_confirm' => '¿Está usted seguro?',
        'disable_success' => "Se desactivaron exitosamente los plugins.",
        'enable_success' => "Se activaron exitosamente los plugins.",
        'unknown_plugin' => "Se eliminó el plugin del sistema de archivos.",
    ],
    'project' => [
        'name' => 'Proyecto',
        'owner_label' => 'Dueño',
        'attach' => 'Adjuntar Proyecto',
        'detach' => 'Separar Proyect',
        'none' => 'Ningun',
        'id' => [
            'label' => 'Identificación del proyecto',
            'help' => '¿Cómo encontrar su ID de Proyecto',
            'missing' => 'Por favor, especifique un ID del proyecto para usar.',
        ],
        'detach_confirm' => '¿Seguro que quieres separar este proyecto?',
        'unbind_success' => 'El proyecto ha sido separado con éxito.',
    ],
    'settings' => [
        'menu_label' => 'Configuración',
        'missing_model' => 'La página de configuración no encuentra una definición de modelo.',
        'update_success' => 'Los ajustes para :name han sido actualizados correctamente.',
        'return' => 'Regresar a la configuración del sistema',
        'search' => 'Buscar'
    ],
    'mail' => [
        'menu_label' => 'Administrar Correo',
        'menu_description' => 'Administrar la configuración de correo electrónico.',
        'general' => 'General',
        'method' => 'Método',
        'sender_name' => 'Nombre del remitente',
        'sender_email' => 'Remitente Email',
        'smtp' => 'SMTP',
        'smtp_address' => 'Dirección SMTP',
        'smtp_authorization' => 'Autorización SMTP requerida',
        'smtp_authorization_comment' => 'Usar esta opción si el servidor SMTP requiere autorización.',
        'smtp_username' => 'Nombre de usuario',
        'smtp_password' => 'Contraseña',
        'smtp_port' => 'Puerto SMTP',
        'smtp_ssl' => 'Conexión SSL requerida',
        'sendmail' => 'Sendmail',
        'sendmail_path' => 'Ruta del Sendmail',
        'sendmail_path_comment' => 'Por favor, especifique la ruta del programa sendmail.',
    ],
    'mail_templates' => [
        'menu_label' => 'Plantillas de Correo',
        'menu_description' => 'Modificar las plantillas de correo que se envían a los usuarios y administradores, administrar diseños de correo electrónico',
        'new_template' => 'Nueva plantilla',
        'new_layout' => 'Nueva Disposición',
        'template' => 'Plantilla',
        'templates' => 'Plantillas',
        'menu_layouts_label' => 'Disposición del Mail',
        'layout' => 'Disposición',
        'layouts' => 'Disposiciones',
        'name' => 'Nombre',
        'name_comment' => 'Nombre único utilizado para referirse a esta plantilla',
        'code' => 'Código',
        'code_comment' => 'Código único utilizado para referirse a esta plantilla ',
        'subject' => 'Asunto',
        'subject_comment' => 'Correo asunto del mensaje',
        'description' => 'Descripción',
        'content_html' => 'HTML',
        'content_css' => 'CSS',
        'content_text' => 'Texto plano',
        'test_send' => 'Enviar mensaje de prueba',
        'test_success' => 'El mensaje de prueba ha sido enviado con éxito.',
        'return' => 'Volver a la lista de plantilla'
    ],
    'install' => [
        'project_label' => 'Adjuntar al proyecto',
        'plugin_label' => 'Instalar Plugin',
        'missing_plugin_name' => 'Por favor, especifique un nombre de Plugin para instalar',
        'install_completing' => 'Finalizó el proceso de instalación',
        'install_success' => 'El plugin se ha instalado correctamente.',
    ],
    'updates' => [
        'title' => 'Administrar actualizaciones',
        'name' => 'Actualizaciones de software',
        'menu_label' => 'Actualizaciones',
        'menu_description' => 'Actualizaciones del sistema, administrar e instalar plugins y temas.',
        'check_label' => 'Chequear actualizaciones',
        'retry_label' => 'Intentar nuevamente',
        'plugin_name' => 'Nombre',
        'plugin_description' => 'Descripción',
        'plugin_version' => 'Versión',
        'plugin_author' => 'Autor',
        'plugin_not_found' => 'Plugin not found',
        'core_current_build' => 'Versión actual',
        'core_build' => 'Versión :build',
        'core_build_help' => 'Última versión está disponible.',
        'core_downloading' => 'Descargando archivos de la aplicación',
        'core_extracting' => 'Descomprimiendo archivos de la aplicación',
        'plugin_downloading' => 'Descargando plugin: :name',
        'plugin_extracting' => 'Descomprimiendo plugin: :name',
        'plugin_version_none' => 'Nuevo plugin',
        'theme_new_install' => 'Intalación de nuevo tema.',
        'theme_downloading' => 'Descargando tema: :name',
        'theme_extracting' => 'Descomprimiendo tema: :name',
        'update_label' => 'Actualizando software',
        'update_completing' => 'Finalizando el proceso de actualización',
        'update_loading' => 'Cargando actualizaciones disponibles...',
        'update_success' => 'El proceso de actualización se realizó exitosamente.',
        'update_failed_label' => 'Actualización falló',
        'force_label' => 'Forzar actualización',
        'found' => [
            'label' => 'Se encontraron nuevas actualizaciones!',
            'help' => 'Click Actualizar software para comenzar con el proceso de actualización.',
        ],
        'none' => [
            'label' => 'No hay actualizaciones',
            'help' => 'No se encontraron nuevas actualizaciones disponibles.',
        ],
    ],
    'server' => [
        'connect_error' => 'Error al conectar con el servidor.',
        'response_not_found' => 'El servidor de actualización no se pudo encontrar.',
        'response_invalid' => 'Respuesta no válida del servidor.',
        'response_empty' => 'Respuesta vacía desde el servidor.',
        'file_error' => 'El servidor no pudo entregar el paquete.',
        'file_corrupt' => 'El archivo se encuentra dañado.',
    ],
    'behavior' => [
        'missing_property' => 'Clase :class debe definir la propiedad $:property utilizada por :behavior comportamiento.',
    ],
    'config' => [
        'not_found' => 'No se puede encontrar el archivo de configuración :file definido por :location.',
        'required' => "Configuración utilizada en :location debe proporcionar un valor. ':property'.",
    ],
    'zip' => [
        'extract_failed' => "No se puede extraer el archivo ':file'.",
    ],
    'event_log' => [
        'hint' => 'Este registro muestra una lista de los posibles errores que se producen en la aplicación, como las excepciones y la información de depuración.',
        'menu_label' => 'Log de eventos',
        'menu_description' => 'Ver los logs de registro del sistema.',
        'empty_link' => 'El registro de eventos se encuentra vacío',
        'empty_loading' => 'Borrando los registros...',
        'empty_success' => 'Los registros fueron borrados',
        'return_link' => 'Regresar al registro de eventos',
        'id' => 'ID',
        'id_label' => 'ID del Evento',
        'created_at' => 'Fecha y Hora',
        'message' => 'Mensaje',
        'level' => 'Nivel',
    ],
    'request_log' => [
        'hint' => 'Este registro muestra una lista de las peticiones del navegador que pueden requerir atención. Por ejemplo, si un usuario abre una página que no se puede encontrar, se crea un registro con el código de estado 404.',
        'menu_label' => 'Logs de acceso',
        'menu_description' => 'Ver listado de redirecciones con errores y paginas No encontradas (404).',
        'empty_link' => 'El registro de accesos se encuentra vacío',
        'empty_loading' => 'Borrando los logs...',
        'empty_success' => 'Los logs fueron borrados...',
        'return_link' => 'Regresar al registro de accesso',
        'id' => 'ID',
        'id_label' => 'ID Log',
        'count' => 'Contador',
        'referer' => 'Referencia',
        'url' => 'URL',
        'status_code' => 'Estado',
    ],
    'permissions' => [
        'manage_system_settings' => 'Gestionar la configuración del sistema',
        'manage_software_updates' => 'Gestionar actualización de software',
        'manage_mail_templates' => 'Gestionar plantillas de correo',
        'manage_other_administrators' => 'Gestionar otros administradores',
        'view_the_dashboard' => 'Ver el Tablero'
    ]
];
