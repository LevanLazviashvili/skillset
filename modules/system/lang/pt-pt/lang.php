<?php

return [
    'app' => [
        'name' => 'October CMS',
        'tagline' => 'Voltando ao básico',
    ],
    'locale' => [
        'be' => 'Bielorusso',
        'bg' => 'Búlgaro',
        'cs' => 'Checo',
        'da' => 'Dinamarquês',
        'en' => 'Inglês (Estados Unidos)',
        'en-au' => 'Inglês (Austrália)',
        'en-ca' => 'Inglês (Canadá)',
        'en-gb' => 'Inglês (Reino Unido)',
        'de' => 'Alemão',
        'el' => 'Grego',
        'es' => 'Espanhol',
        'es-ar' => 'Espanhol (Argentina)',
        'fa' => 'Persa (Farsi)',
        'fr' => 'Françês',
        'fr-ca' => 'Françês (Canadá)',
        'hu' => 'Húngaro',
        'id' => 'Indonésio',
        'it' => 'Italiano',
        'ja' => 'Japonês',
        'lv' => 'Letão',
        'nb-no' => 'Norueguês',
        'nl' => 'Holandês',
        'pl' => 'Polaco',
        'pt-pt' => 'Português (Portugal)',
        'pt-br' => 'Português (Brasil)',
        'ro' => 'Romeno',
        'rs' => 'Srpski',
        'ru' => 'Russo',
        'sv' => 'Suéco',
        'sk' => 'Esloveno',
        'sl' => 'Slovenščina',
        'tr' => 'Turco',
        'zh-cn' => 'Chinês',
        'zh-tw' => 'Tailandês',
        'vn' => 'Tiếng việt'
    ],
    'directory' => [
        'create_fail' => 'Não é possível criar a diretoria: :name',
    ],
    'file' => [
        'create_fail' => 'Não é possível criar o ficheiro: :name',
    ],
    'page' => [
        'invalid_token' => [
            'label' => 'Token de segurança inválido',
        ],
    ],
    'combiner' => [
        'not_found' => 'O ficheiro combinador ":name" não foi encontrado.',
    ],
    'system' => [
        'name' => 'Sistema',
        'menu_label' => 'Sistema',
        'categories' => [
            'cms' => 'CMS',
            'misc' => 'Diversos',
            'logs' => 'Registos',
            'mail' => 'E-mail',
            'shop' => 'Loja ',
            'team' => 'Equipa',
            'users' => 'Utilizadores',
            'system' => 'Sistema',
            'social' => 'Social',
            'events' => 'Eventos',
            'customers' => 'Clientes',
            'my_settings' => 'Configurações',
        ]
    ],
    'theme' => [
        'label' => 'Tema',
        'unnamed' => 'Tema sem nome',
        'name' => [
            'label' => 'Nome do Tema',
            'help' => 'O nome do tema deve ser único. Por exemplo, RainLab.Vanilla'
        ],
    ],
    'themes' => [
        'install' => 'Instalar tema',
        'search' => 'Procurar temas para instalar...',
        'installed' => 'Temas instalados',
        'no_themes' => 'Não há temas instalados.',
        'recommended' => 'Recomendado',
        'remove_confirm' => 'Tem a certeza que deseja remover este tema?'
    ],
    'plugin' => [
        'label' => 'Extensão',
        'unnamed' => 'Extensão sem nome',
        'name' => [
            'label' => 'Nome da extensão',
            'help' => 'Nomeie a extensão pelo seu código exclusivo. Por exemplo, RainLab.Blog',
        ],
    ],
    'plugins' => [
        'manage' => 'Gerir extensões',
        'enable_or_disable' => 'Activar ou desactivar',
        'enable_or_disable_title' => 'Activar ou desactivar extensões',
        'install' => 'Instalar extensões',
        'install_products' => 'Instalar produtos',
        'search' => 'Procurar extensão para instalar...',
        'installed' => 'Extensões instaladas',
        'no_plugins' => 'Não há extensões instaladas.',
        'recommended' => 'Recomendada',
        'remove' => 'Remover',
        'refresh' => 'Actualizar',
        'disabled_label' => 'Desactivado',
        'disabled_help' => 'Extensões que estão desactivadas são ignoradas pela aplicação.',
        'frozen_label' => 'Congelar actualizações',
        'frozen_help' => 'Extensões congeladas serão ignoradas pelo processo de atualização.',
        'selected_amount' => 'Extensões selecionadas: :amount',
        'remove_confirm' => 'Tem a certeza?',
        'remove_success' => 'Extensões removidas com sucesso do sistema.',
        'refresh_confirm' => 'Tem a certeza?',
        'refresh_success' => 'Extensões atualizadas com sucesso.',
        'disable_confirm' => 'Tem a certeza?',
        'disable_success' => 'Extensões desactivadas com sucesso.',
        'enable_success' => 'Extensões desactivadas com sucesso.',
        'unknown_plugin' => 'Extensão removida do sistema de ficheiros.',
    ],
    'project' => [
        'name' => 'Projecto',
        'owner_label' => 'Desenvolvedor',
        'attach' => 'Anexar Projecto',
        'detach' => 'Desanexar Projecto',
        'none' => 'Nenhum',
        'id' => [
            'label' => 'Identificador do Projeto',
            'help' => 'Como encontrar o identificador do projecto',
            'missing' => 'Por favor, forneça um identificador de projecto para utilizar.',
        ],
        'detach_confirm' => 'Tem a certeza que deseja desanexar este projecto?',
        'unbind_success' => 'Projecto desanexado com sucesso.',
    ],
    'settings' => [
        'menu_label' => 'Configurações',
        'not_found' => 'Impossível encontrar as configurações solicitadas.',
        'missing_model' => 'Falta uma definição de modelo na página de configurações.',
        'update_success' => 'Configurações para :name foram atualizados com sucesso.',
        'return' => 'Regressar para as configurações do sistema',
        'search' => 'Procurar',
    ],
    'mail' => [
        'log_file' => 'Ficheiro de registo',
        'menu_label' => 'Configurações de E-mail',
        'menu_description' => 'Gerir configurações de e-mail.',
        'general' => 'Geral',
        'method' => 'Método de Envio',
        'sender_name' => 'Nome do Remetente',
        'sender_email' => 'E-mail do Remetente',
        'php_mail' => 'PHP mail',
        'smtp' => 'SMTP',
        'smtp_address' => 'Endereço SMTP',
        'smtp_authorization' => 'Autenticação SMTP obrigatória',
        'smtp_authorization_comment' => 'Use esta opção se o seu servidor SMTP requer autenticação.',
        'smtp_username' => 'Utilizador',
        'smtp_password' => 'Senha',
        'smtp_port' => 'Porta SMTP',
        'smtp_ssl' => 'Conexão SSL obrigatória',
        'smtp_encryption' => 'Protocolo de criptografia SMTP',
        'smtp_encryption_none' => 'Sem criptografia',
        'smtp_encryption_tls' => 'TLS',
        'smtp_encryption_ssl' => 'SSL',
        'sendmail' => 'Sendmail',
        'sendmail_path' => 'Caminho do Sendmail',
        'sendmail_path_comment' => 'Por favor, especifique o caminho do programa Sendmail.',
        'mailgun' => 'Mailgun',
        'mailgun_domain' => 'Domínio do Mailgun',
        'mailgun_domain_comment' => 'Por favor, forneça o domínio do Mailgun.',
        'mailgun_secret' => 'Mailgun Secret',
        'mailgun_secret_comment' => 'Forneça sua chave de API do Mailgun.',
        'mandrill' => 'Mandrill',
        'mandrill_secret' => 'Mandrill Secret',
        'mandrill_secret_comment' => 'Forneça sua chave de API do Mandrill',
        'ses' => 'SES',
        'ses_key' => 'Chave SES',
        'ses_key_comment' => 'Forneça sua chave do SES',
        'ses_secret' => 'SES Secret',
        'ses_secret_comment' => 'Forneça sua chave de API do SES.',
        'ses_region' => 'Região SES',
        'ses_region_comment' => 'Entre com sua região SES (exemplo: us-east-1)',
        'drivers_hint_header' => 'Drivers não instalados',
        'drivers_hint_content' => 'Este método requer que a extensão ":plugin" esteja instalada.'
    ],
    'mail_templates' => [
        'menu_label' => 'Modelos de E-mail',
        'menu_description' => 'Modificar os modelos dos e-mails que são enviados para utilizadores e administradores.',
        'new_template' => 'Novo modelo',
        'new_layout' => 'Novo esboço',
        'template' => 'Modelo',
        'templates' => 'Modelos',
        'menu_layouts_label' => 'Esboços de e-mail',
        'layout' => 'Esboço',
        'layouts' => 'Esboços',
        'no_layout' => '-- Sem esboço --',
        'name' => 'Nome',
        'name_comment' => 'Nome exclusivo utilizado para se referir a este modelo',
        'code' => 'Código',
        'code_comment' => 'Código exclusivo utilizado para se referir a este modelo',
        'subject' => 'Assunto',
        'subject_comment' => 'Assunto da mensagem',
        'description' => 'Descrição',
        'content_html' => 'HTML',
        'content_css' => 'CSS',
        'content_text' => 'Texto Simples',
        'test_send' => 'Enviar mensagem de teste',
        'test_success' => 'Mensagem de teste enviada com sucesso.',
        'test_confirm' => 'Enviar uma mensagem de teste para :email. Continuar?',
        'creating' => 'Criando modelo...',
        'creating_layout' => 'Criando esboço...',
        'saving' => 'Guardando modelo...',
        'saving_layout' => 'Guardando esboço...',
        'delete_confirm' => 'Apagar este modelo?',
        'delete_layout_confirm' => 'Apagar este esboço?',
        'deleting' => 'Apagando modelo...',
        'deleting_layout' => 'Apagando esboço...',
        'sending' => 'Enviando mensagem de teste...',
        'return' => 'Regressar à lista de modelos'
    ],
    'install' => [
        'project_label' => 'Anexar ao projecto',
        'plugin_label' => 'Instalar extensão',
        'theme_label' => 'Instalar tema',
        'missing_plugin_name' => 'Por favor, especifique um nome da extensão para instalar.',
        'missing_theme_name' => 'Por favor, especifique um nome de tema para instalar.',
        'install_completing' => 'Finalizando o processo de instalação',
        'install_success' => 'A extensão foi instalada com sucesso.',
    ],
    'updates' => [
        'title' => 'Gerir actualizações',
        'name' => 'Actualização de software',
        'menu_label' => 'Actualizações',
        'menu_description' => 'Actualize o sistema, gira e instale extensões e temas.',
        'return_link' => 'Voltar às actualizações',
        'check_label' => 'Verificar actualizações',
        'retry_label' => 'Tentar novamente',
        'plugin_name' => 'Nome',
        'plugin_code' => 'Código',
        'plugin_description' => 'Descrição',
        'plugin_version' => 'Versão',
        'plugin_author' => 'Autor',
        'plugin_not_found' => 'Extensão não encontrada',
        'core_current_build' => 'Compilação atual',
        'core_build' => 'Compilação :build',
        'core_build_help' => 'Última versão está disponível.',
        'core_downloading' => 'Descarregando ficheiros da aplicação',
        'core_extracting' => 'Descomprimindo ficheiros do aplicação',
        'plugins' => 'Extensões',
        'themes' => 'Temas',
        'disabled' => 'Desactivados',
        'plugin_downloading' => 'Baixando a extensão: :name',
        'plugin_extracting' => 'Descomprimindo a extensão: :name',
        'plugin_version_none' => 'Nova extensão',
        'plugin_current_version' => 'Versão actual',
        'theme_new_install' => 'Instalação do novo tema.',
        'theme_downloading' => 'Descarregando o tema: :name',
        'theme_extracting' => 'Descomprimindo o tema: :name',
        'update_label' => 'Actualizar',
        'update_completing' => 'Finalizando processo de actualização',
        'update_loading' => 'Carregando atualizações disponíveis...',
        'update_success' => 'O processo de actualização foi realizado com sucesso.',
        'update_failed_label' => 'Falha na actualização',
        'force_label' => 'Forçar actualização',
        'found' => [
            'label' => 'Actualizações encontradas!',
            'help' => 'Clique em Actualizar para iniciar o processo de actualização.',
        ],
        'none' => [
            'label' => 'Nenhuma actualização',
            'help' => 'Não há novas actualizações.',
        ],
        'important_action' => [
            'empty' => 'Selecionar acção',
            'confirm' => 'Confirmar actualização',
            'skip' => 'Ignorar esta actualização (apenas uma vez)',
            'ignore' => 'Ignorar esta actualização (sempre)',
        ],
        'important_action_required' => 'Acção requerida',
        'important_view_guide' => 'Exibir guia de actualização',
        'important_view_release_notes' => 'Ver notas da actualização',
        'important_alert_text' => 'Algumas actualizações precisam de sua atenção.',
        'details_title' => 'Detalhes da extensão',
        'details_view_homepage' => 'Visualizar página',
        'details_readme' => 'Documentação',
        'details_readme_missing' => 'Não foi fornecida nenhuma documentação.',
        'details_changelog' => 'Registo de alterações',
        'details_changelog_missing' => 'Não foi fornecido registo de alterações.',
        'details_upgrades' => 'Guia de actualização',
        'details_upgrades_missing' => 'Não existem instruções de actualização.',
        'details_licence' => 'Licença',
        'details_licence_missing' => 'Não foi fornecida licença.',
        'details_current_version' => 'Versão actual',
        'details_author' => 'Autor',
    ],
    'server' => [
        'connect_error' => 'Erro ao conectar-se com o servidor.',
        'response_not_found' => 'O servidor de actualização não foi encontrado.',
        'response_invalid' => 'Resposta inválida do servidor.',
        'response_empty' => 'Resposta vazia do servidor.',
        'file_error' => 'Servidor não conseguiu entregar o pacote.',
        'file_corrupt' => 'O ficheiro do servidor está corrompido.',
    ],
    'behavior' => [
        'missing_property' => 'Classe :class deve definir a propriedade $:property usada pelo comportamento :behavior.',
    ],
    'config' => [
        'not_found' => 'Não foi possível localizar o ficheiro de configuração :file definido para :location.',
        'required' => 'Configuração utilizada em :location deve fornecer um valor :property.',
    ],
    'zip' => [
        'extract_failed' => 'Não foi possível extrair ficheiro do núcleo ":file".',
    ],
    'event_log' => [
        'hint' => 'Este registo mostra a lista dos potenciais erros que ocorreram na aplicação, como exceções e informações de depuração.',
        'menu_label' => 'Registo de Eventos',
        'menu_description' => 'Visualize as mensagens do sistema, com horário e detalhes.',
        'empty_link' => 'Esvaziar registo de eventos',
        'empty_loading' => 'Esvaziando registo de eventos...',
        'empty_success' => 'Registo de eventos esvaziado com sucesso.',
        'return_link' => 'Regressar ao registo de eventos',
        'id' => 'ID',
        'id_label' => 'Identificador do Evento',
        'created_at' => 'Data & Hora',
        'message' => 'Mensagem',
        'level' => 'Nível',
        'preview_title' => 'Evento'
    ],
    'request_log' => [
        'hint' => 'Este registro mostra uma lista de requisições que requerem atenção. Por exemplo, se um utilizador solicitar uma página não encontrada, será registado com o status 404.',
        'menu_label' => 'Registo de Requisições',
        'menu_description' => 'Visualize requisições mal sucedidas na aplicação, como Página não encontrada (404).',
        'empty_link' => 'Esvaziar registo de requisições.',
        'empty_loading' => 'Esvaziando registo de requisições...',
        'empty_success' => 'Registo de requisições esvaziado com sucesso.',
        'return_link' => 'Regressar ao registo de requisições',
        'id' => 'ID',
        'id_label' => 'ID do registo',
        'count' => 'Contador',
        'referer' => 'Referências',
        'url' => 'URL',
        'status_code' => 'Estado',
        'preview_title' => 'Requisição'
    ],
    'permissions' => [
        'name' => 'Sistema',
        'manage_system_settings' => 'Gerir configurações do sistema',
        'manage_software_updates' => 'Gerir actualizações',
        'access_logs' => 'Exibir registos de sistema',
        'manage_mail_templates' => 'Gerir modelos de e-mail',
        'manage_mail_settings' => 'Gerir configurações de e-mail',
        'manage_other_administrators' => 'Gerir outros administradores',
        'manage_preferences' => 'Gerir preferências da área administrativa',
        'manage_editor' => 'Gerir preferências do editor de código',
        'view_the_dashboard' => 'Visualizar o painel',
        'manage_branding' => 'Personalizar o backend'
    ],
    'log' => [
        'menu_label' => 'Configurações de registo',
        'menu_description' => 'Especifique que áreas devem ter registo.',
        'default_tab' => 'Registos',
        'log_events' => 'Registo de eventos de sistema',
        'log_events_comment' => 'Armazenar eventos na base de dados além do registo em ficheiro.',
        'log_requests' => 'Registar requisições inválidas',
        'log_requests_comment' => 'Requisições que requerem a sua atenção, por exemplo erros 404.',
        'log_theme' => 'Registar alterações de tema',
        'log_theme_comment' => 'Quado uma alteração é efectuada no tema utilizando o backend.',
    ],
    'media' => [
        'invalid_path' => "Caminho especificado inválido: ':path'.",
        'folder_size_items' => 'item(s)',
    ],
];
