# Whitepaper Técnico: La API de Habilidades (Abilities API) de WordPress
## 1.0 Introducción: Redefiniendo la Funcionalidad en WordPress
El ecosistema de WordPress, reconocido por su inmensa flexibilidad, enfrenta un desafío fundamental derivado de su propio éxito: la fragmentación de la funcionalidad. A lo largo de dos décadas, su poder se ha distribuido en miles de funciones, hooks y endpoints de API personalizados, diseminados entre el núcleo, los temas y los plugins. Esta heterogeneidad ha creado silos de funcionalidad, limitando la interoperabilidad, obstaculizando la automatización y dificultando la integración con sistemas modernos, especialmente con agentes de inteligencia artificial (IA) que requieren una forma estandarizada de comprender y actuar sobre las capacidades de un sitio.
La API de Habilidades (Abilities API) es la respuesta estratégica de WordPress a este desafío. Introducida como un nuevo sistema fundacional en WordPress 6.9, esta API establece un lenguaje común para que todos los componentes del ecosistema —núcleo, plugins y temas— puedan declarar sus capacidades de una manera unificada. Funciona como un registro centralizado y estandarizado de "habilidades", cada una definida con un esquema, permisos y lógica de ejecución en un formato legible tanto por humanos como por máquinas. Esta iniciativa es una pieza clave del proyecto más amplio "AI Building Blocks for WordPress", diseñado para transformar WordPress de un conjunto de funcionalidades aisladas a un sistema interconectado y programáticamente discernible.
El propósito de este whitepaper es proporcionar un análisis técnico exhaustivo de la API de Habilidades. A lo largo de este documento, exploraremos su arquitectura, sus componentes clave y su impacto estratégico. Está dirigido a desarrolladores, arquitectos de sistemas y tomadores de decisiones técnicas que buscan comprender cómo esta nueva API no solo resuelve problemas existentes, sino que también sienta las bases para la próxima generación de aplicaciones y flujos de trabajo en el ecosistema de WordPress.
Para apreciar plenamente el potencial transformador de la API de Habilidades, es esencial comenzar por sus fundamentos. A continuación, analizaremos los objetivos de diseño que guiaron su creación y los conceptos arquitectónicos que definen su estructura.
## 2.0 Fundamentos Arquitectónicos y Objetivos de Diseño
Una arquitectura de software robusta se define por sus objetivos de diseño. En el caso de la API de Habilidades, estos objetivos no son meramente técnicos; reflejan una visión a largo plazo para un ecosistema de WordPress más interconectado, seguro y preparado para el futuro. Cada decisión arquitectónica fue tomada para abordar problemas históricos y habilitar nuevas clases de funcionalidad, desde la automatización de flujos de trabajo hasta la integración nativa con IA.
La arquitectura de la API se sustenta en cuatro pilares fundamentales:
Descubrimiento (Discoverability): El objetivo principal es eliminar la necesidad de "adivinar" funcionalidades o de aplicar ingeniería inversa al código de un plugin. Cada habilidad registrada puede ser listada, consultada e inspeccionada a través de una interfaz estándar. Esto permite que herramientas de automatización, agentes de IA y otros componentes del sistema puedan descubrir dinámicamente qué acciones están disponibles en un sitio determinado y cómo ejecutarlas.
Interoperabilidad (Interoperability): Al proporcionar un esquema uniforme, la API permite que componentes no relacionados —como plugins de diferentes autores— puedan componer flujos de trabajo complejos. Las habilidades están diseñadas como unidades atómicas y enfocadas que pueden combinarse en entidades más grandes. La analogía es similar a cómo los bloques de Gutenberg se componen para formar patrones: múltiples habilidades pueden encadenarse para crear un flujo de trabajo completo y automatizado.
Seguridad Primero (Security-first): La seguridad es un requisito no negociable. Cada habilidad debe definir explícitamente sus permisos a través de un permission_callback. Esta función se integra directamente con el sistema de capacidades existente de WordPress (current_user_can()), garantizando que solo los usuarios o sistemas con la autorización adecuada puedan invocar una habilidad. No se puede registrar una habilidad sin una verificación de permisos explícita.
Adopción Gradual (Gradual Adoption): Para facilitar una transición suave y fomentar la experimentación temprana, la API fue lanzada inicialmente como un paquete de Composer. Esta estrategia permitió a los desarrolladores comenzar a integrar la API en sus proyectos antes de su inclusión formal en el núcleo de WordPress, asegurando una base de código más madura y probada en el momento de su lanzamiento oficial.
La API introduce una terminología precisa para describir sus componentes. La siguiente tabla define los conceptos clave:
Concepto Clave
Descripción Técnica
Habilidad (Ability)
Una unidad autocontenida de funcionalidad con un nombre único (namespace/ability-name), esquema de entrada/salida, permisos y lógica de ejecución. Es una instancia de la clase WP_Ability.
Categoría (Category)
Una forma de organizar habilidades relacionadas. Cada habilidad debe pertenecer a una categoría. Es una instancia de la clase WP_Ability_Category.
Registro (Registry)
Un objeto singleton central (WP_Abilities_Registry) que almacena todas las habilidades registradas y proporciona métodos para su gestión.
Esquema (Schema)
Una definición en formato JSON Schema para las entradas y salidas esperadas de una habilidad, permitiendo la validación automática y la autodocumentación.
Callback de Ejecución
La función o método PHP que contiene la lógica de negocio y se ejecuta cuando se invoca una habilidad.
Callback de Permiso
La función que determina si el usuario actual tiene permiso para ejecutar una habilidad específica, integrándose con el sistema de capacidades de WordPress.

Con estos conceptos arquitectónicos establecidos, podemos ahora examinar cómo se manifiestan en la implementación técnica de la API, explorando los componentes de backend y frontend que los desarrolladores utilizarán para interactuar con este nuevo sistema.
## 3.0 Componentes Clave de la API
La arquitectura de la API de Habilidades se materializa a través de una estructura tripartita diseñada para garantizar una funcionalidad consistente en todos los contextos de WordPress. Esta estructura consta de tres componentes interconectados: una API de PHP para la lógica del lado del servidor, endpoints de la API REST para la comunicación externa y un cliente JavaScript para la interacción en el frontend. Este diseño asegura que una habilidad registrada sea accesible de manera uniforme, ya sea desde otro plugin en el backend, una aplicación externa o una interfaz de usuario en el navegador.
### 3.2 Detalle de la API de PHP para el Servidor
#### 3.2.1 Funciones de Gestión y Hooks
El núcleo de la API reside en un conjunto de funciones PHP que permiten a los desarrolladores registrar y gestionar habilidades y sus categorías.
Gestión de Habilidades:
wp_register_ability(): Registra una nueva habilidad en el sistema.
wp_get_ability(): Recupera un objeto de habilidad específico por su nombre.
wp_get_abilities(): Devuelve un arreglo de todas las habilidades registradas.
wp_has_ability(): Verifica si una habilidad está registrada.
wp_unregister_ability(): Elimina una habilidad del registro.
Gestión de Categorías:
wp_register_ability_category(): Registra una nueva categoría para organizar habilidades.
wp_get_ability_category(): Recupera un objeto de categoría específico.
wp_get_ability_categories(): Devuelve un arreglo de todas las categorías registradas.
El registro debe realizarse de manera ordenada utilizando los hooks de acción proporcionados: wp_abilities_api_categories_init para registrar categorías y wp_abilities_api_init para registrar habilidades. Es obligatorio utilizar estos hooks para garantizar que los registros estén disponibles cuando el sistema los necesite.
### 3.3 Análisis de los Endpoints de la API REST
#### 3.3.1 Exposición y Acceso Remoto
Por defecto, las habilidades no se exponen a través de la API REST para mantener un principio de seguridad de "mínimo privilegio". Para habilitar el acceso remoto a una habilidad, el desarrollador debe establecer explícitamente el argumento meta.show_in_rest en true durante el registro.
Una vez habilitados, los endpoints están disponibles bajo el namespace wp-abilities/v1. El acceso a estos endpoints requiere que el usuario esté autenticado, utilizando los métodos de autenticación estándar de WordPress (cookies o contraseñas de aplicación).
Los principales endpoints disponibles son:
GET /wp-abilities/v1/categories: Lista todas las categorías de habilidades.
GET /wp-abilities/v1/categories/{slug}: Obtiene los detalles de una categoría específica.
GET /wp-abilities/v1/abilities: Lista todas las habilidades expuestas.
GET /wp-abilities/v1/abilities/{name}: Obtiene los detalles de una habilidad específica.
POST /wp-abilities/v1/abilities/{name}/run: Ejecuta una habilidad específica (otros métodos HTTP como GET o DELETE pueden ser aplicables).
### 3.4 Descripción del Cliente JavaScript para el Frontend
#### 3.4.1 Interacción desde el Navegador
La API de Habilidades incluye un cliente JavaScript diseñado para interactuar con el sistema desde el frontend. Este cliente, que se distribuirá como un paquete de Gutenberg (@wordpress/abilities), permite a los desarrolladores de bloques y otras interfaces de usuario descubrir y ejecutar habilidades de manera nativa.
El cliente puede descubrir todas las habilidades registradas, tanto las definidas en el servidor (PHP) como las que podrían registrarse directamente en el cliente. De manera crucial, permite invocar y ejecutar habilidades PHP del lado del servidor, actuando como un puente transparente hacia la lógica del backend.
La descripción técnica de estos componentes revela una API robusta y versátil. Sin embargo, su verdadero valor se comprende mejor al analizar los casos de uso estratégicos que esta nueva arquitectura habilita, los cuales exploraremos a continuación.
## 4.0 Casos de Uso Estratégicos y el Futuro del Ecosistema
La API de Habilidades es más que una simple herramienta para desarrolladores; es un pilar fundamental para la evolución de WordPress. Su valor no reside únicamente en la estandarización del código, sino en su capacidad para habilitar una nueva generación de integraciones, flujos de trabajo automatizados e interacciones inteligentes que antes eran complejas o inviables. Esta API posiciona a WordPress para liderar en un entorno web cada vez más componible y automatizado.
### 4.2 Profundización en la Integración con Inteligencia Artificial (IA)
Uno de los principales impulsores de la API es la integración con sistemas de inteligencia artificial. La API proporciona un mecanismo seguro y estructurado para que los agentes de IA descubran y utilicen las capacidades de un sitio WordPress. Esto es posible gracias a una arquitectura de componentes que incluye:
El Protocolo de Contexto de Modelo (MCP): Un estándar abierto diseñado para que los asistentes de IA, como Claude o ChatGPT, se conecten de forma segura con herramientas externas.
El MCP Adapter: Este componente actúa como un traductor. Toma las habilidades registradas en WordPress y las expone en un formato compatible con el MCP. Para el asistente de IA, cada habilidad de WordPress aparece como una "herramienta" que puede invocar para realizar tareas, como "publicar un borrador" o "analizar el SEO de un contenido".
Esta integración transforma a WordPress de una plataforma pasiva a un participante activo en flujos de trabajo impulsados por IA.
### 4.3 Análisis de la Interoperabilidad entre Plugins y Flujos de Trabajo
Históricamente, los plugins de WordPress han operado en "silos de funcionalidad", con poca o ninguna conciencia de las capacidades de otros plugins instalados. La API de Habilidades rompe estas barreras al crear un lenguaje común.
Al registrar sus capacidades como habilidades, los plugins pueden descubrir y componer programáticamente las funcionalidades de otros. Esto permite la creación de flujos de trabajo complejos y automatizados que antes requerían integraciones personalizadas y frágiles. Por ejemplo, un plugin de comercio electrónico podría invocar una habilidad de un plugin de SEO para analizar la descripción de un producto antes de publicarlo. Estos flujos de trabajo no se limitan a la IA; también pueden potenciar herramientas orientadas al usuario, como el Command Palette, que puede listar y ejecutar habilidades directamente desde la interfaz de administración.
### 4.4 Comparativa con APIs Existentes
Para clarificar el rol de la API de Habilidades, es útil compararla con otras APIs fundamentales de WordPress. La siguiente tabla utiliza una analogía para ilustrar sus propósitos distintos pero complementarios.
API
Propósito Principal
Analogía
API de Capacidades
Define quién puede realizar una acción (control de permisos basado en roles).
Las llaves que otorgan acceso.
API REST
Expone datos y funcionalidades a sistemas externos a través de HTTP.
La puerta para entrar y salir del edificio.
API de Habilidades (Abilities)
Crea un registro de qué acciones se pueden realizar (descubrimiento y ejecución).
El mapa del edificio que muestra las salas.

Después de explorar el "porqué" estratégico detrás de la API, es el momento de centrarse en el "cómo". La siguiente sección proporcionará una guía de implementación práctica para que los desarrolladores comiencen a integrar esta poderosa API en sus propios proyectos.
## 5.0 Guía de Implementación para Desarrolladores
La adopción de la API de Habilidades ha sido diseñada para ser un proceso sencillo y estructurado. El objetivo de esta sección es proporcionar a los desarrolladores el conocimiento práctico necesario para comenzar a registrar y utilizar habilidades en sus propios plugins y temas, transformando la funcionalidad aislada en capacidades descubribles e interoperables.
### 5.2 Proceso de Registro de una Habilidad
#### 5.2.1 Registro de Categorías y Habilidades
El primer paso es registrar una categoría y, posteriormente, una habilidad dentro de esa categoría. Esto debe hacerse utilizando los hooks de acción wp_abilities_api_categories_init y wp_abilities_api_init, respectivamente.
El siguiente ejemplo completo muestra cómo registrar una habilidad para obtener la cantidad de entradas publicadas.

```php
<?php

/**
 * Registra categorías de habilidades personalizadas.
 * Se ejecuta en el hook 'wp_abilities_api_categories_init'.
 */
add_action( 'wp_abilities_api_categories_init', 'my_plugin_register_ability_categories' );

function my_plugin_register_ability_categories() {
    wp_register_ability_category(
        'content-management',
        array(
            'label'       => __( 'Content Management', 'my-plugin' ),
            'description' => __( 'Abilities for managing and organizing content.', 'my-plugin' ),
        )
    );
}

/**
 * Registra habilidades personalizadas.
 * Se ejecuta en el hook 'wp_abilities_api_init'.
 */
add_action( 'wp_abilities_api_init', 'my_plugin_register_abilities' );

function my_plugin_register_abilities() {
    wp_register_ability(
        'my-plugin/get-post-count', // Nombre único: namespace/nombre-habilidad
        array(
            'label'               => __( 'Get Post Count', 'my-plugin' ),
            'description'         => __( 'Retrieves the total number of published posts.', 'my-plugin' ),
            'category'            => 'content-management',
            'input_schema'        => array(
                'type'        => 'string',
                'description' => __( 'The post type to count.', 'my-plugin' ),
                'default'     => 'post',
            ),
            'output_schema'       => array(
                'type'        => 'integer',
                'description' => __( 'The number of published posts.', 'my-plugin' ),
            ),
            'execute_callback'    => 'my_plugin_get_post_count',
            'permission_callback' => function() {
                return current_user_can( 'read' );
            },
            'meta' => array(
                'show_in_rest' => true, // Expone esta habilidad en la API REST
            ),
        )
    );
}

/**
 * Callback de ejecución para la habilidad 'get-post-count'.
 *
 * @param string $input El tipo de post para contar.
 * @return int El número de posts publicados.
 */
function my_plugin_get_post_count( $input ) {
    $post_type = $input ?? 'post';
    $count     = wp_count_posts( $post_type );
    return (int) $count->publish;
}
```

A continuación, se desglosan los argumentos clave de wp_register_ability():
label: Un nombre legible para la habilidad.
description: Una breve explicación de lo que hace la habilidad.
category: El slug de la categoría a la que pertenece la habilidad.
input_schema: Un arreglo que define la estructura de los datos de entrada usando JSON Schema.
output_schema: Un arreglo que define la estructura de los datos de salida.
execute_callback: El nombre de la función PHP que contiene la lógica de negocio.
permission_callback: Una función que devuelve true o false para determinar si el usuario actual puede ejecutar la habilidad.
meta: Un arreglo opcional para almacenar metadatos adicionales, como show_in_rest para exponer la habilidad en la API REST.
### 5.3 Ejecución y Consumo de Habilidades
#### 5.3.1 Métodos de Ejecución
Una vez registrada, una habilidad puede ser ejecutada desde diferentes contextos:
#### 5.3.2 Desde PHP: Se obtiene la habilidad con wp_get_ability() y se ejecuta con el método ->execute().
#### 5.3.3 Desde la API REST: Se realiza una solicitud POST al endpoint /run de la habilidad.
#### 5.3.4 Desde JavaScript: Se utiliza la función executeAbility del paquete @wordpress/abilities.
### 5.4 Convenciones de Nomenclatura y Mejores Prácticas
#### 5.4.1 Para garantizar la coherencia y evitar conflictos, se recomienda seguir estas prácticas:
Usar un namespace: Prefije siempre el nombre de su habilidad con un identificador único, típicamente el slug de su plugin (ej. mi-plugin/mi-habilidad), para prevenir colisiones.
Utilizar caracteres permitidos: Los nombres deben usar únicamente caracteres alfanuméricos en minúsculas, guiones (-) y una barra (/) para separar el namespace.
Emplear nombres descriptivos: Los nombres deben ser claros y orientados a la acción (ej. generar-reporte en lugar de reporte).
Mantener las habilidades "atómicas": Diseñe habilidades que realicen una sola tarea bien definida. Esto las hace más reutilizables y fáciles de componer en flujos de trabajo complejos.
Esta guía ha cubierto los pasos esenciales para implementar la API de Habilidades. Con esta base práctica, estamos listos para concluir con una reflexión sobre el impacto a largo plazo de esta API en el futuro de WordPress.
## 6.0 Conclusión: Hacia un WordPress Unificado y Extensible
La API de Habilidades representa un cambio de paradigma fundamental para WordPress. Abandona un modelo de funcionalidades aisladas y a menudo opacas en favor de un sistema interconectado, estandarizado y programáticamente discernible. Al establecer un registro central de capacidades con esquemas, permisos y lógica de ejecución claros, la API resuelve el desafío histórico de la fragmentación y sienta las bases para un ecosistema más cohesionado y potente.
El impacto de esta API trascenderá la simple mejora de la experiencia del desarrollador. Es la infraestructura fundamental que permitirá a WordPress no solo competir, sino liderar en un entorno web cada vez más dominado por la automatización, la inteligencia artificial y las arquitecturas componibles. Al proporcionar un "mapa" de lo que un sitio WordPress puede hacer, la API desbloquea el potencial para crear flujos de trabajo inteligentes, integraciones de IA nativas y una interoperabilidad sin precedentes entre miles de plugins y temas. Esta es la base sobre la cual se construirá la próxima década de innovación en WordPress.
El éxito y la evolución de la API de Habilidades dependen de la participación activa de la comunidad de desarrolladores de WordPress. Se anima a todos los creadores de plugins y temas a experimentar con la API, a comenzar a registrar las funcionalidades de sus productos como habilidades y a compartir sus casos de uso y retroalimentación. La discusión continúa en el canal #core-ai del Slack de WordPress, un espacio abierto para colaborar y ayudar a dar forma al futuro de esta tecnología transformadora.
=====================================================================

Aquí tienes un ejemplo práctico y completo de cómo implementar la Abilities API (API de Habilidades), la cual estará disponible en WordPress 6.9.
Este ejemplo muestra cómo registrar una habilidad personalizada que permite obtener el conteo de publicaciones (posts) de un sitio. Este proceso implica tres pasos principales: registrar una categoría, registrar la habilidad y definir la lógica de ejecución.

### 1. Preparación y Registro de la Categoría
Antes de registrar una habilidad, debes asignarla a una categoría para mantener el orden. Esto se hace utilizando el hook wp_abilities_api_categories_init.

```php
add_action( 'wp_abilities_api_categories_init', 'mi_plugin_registrar_categorias' );
/**
 * Registrar categorías de habilidades.
 */
function mi_plugin_registrar_categorias() {
    wp_register_ability_category( 'gestion-contenido', array(
        'label'       => __( 'Gestión de Contenido', 'mi-plugin' ),
        'description' => __( 'Habilidades para gestionar y organizar contenido.', 'mi-plugin' ),
    ) );
}
```
Este código crea una categoría llamada gestion-contenido que agrupará funciones relacionadas.
### 2. Registro de la Habilidad (Ability)
Una vez definida la categoría, registramos la habilidad específica usando wp_register_ability dentro del hook wp_abilities_api_init. Aquí definimos los esquemas de entrada (input) y salida (output) usando JSON Schema, lo cual permite que herramientas externas (como agentes de IA) entiendan qué datos enviar y qué recibir,.


```php
add_action( 'wp_abilities_api_init', 'mi_plugin_registrar_habilidades' );
/**
 * Registrar habilidades.
 */
function mi_plugin_registrar_habilidades() {
    wp_register_ability( 'mi-plugin/obtener-conteo-posts', array(
        'label'           => __( 'Obtener Conteo de Posts', 'mi-plugin' ),
        'description'     => __( 'Recupera el número total de publicaciones publicadas.', 'mi-plugin' ),
        'category'        => 'gestion-contenido', // Debe coincidir con la categoría registrada arriba
        
        // Definición de qué datos necesita la habilidad para funcionar
        'input_schema'    => array(
            'type'        => 'string',
            'description' => __( 'El tipo de post a contar (ej. post, page).', 'mi-plugin' ),
            'default'     => 'post',
        ),
        
        // Definición de qué datos devolverá la habilidad
        'output_schema'   => array(
            'type'        => 'integer',
            'description' => __( 'El número de publicaciones publicadas.', 'mi-plugin' ),
        ),
        
        'execute_callback'    => 'mi_plugin_ejecutar_conteo', // La función PHP real
        'permission_callback' => function() {
            // Solo usuarios que pueden leer contenido pueden usar esto
            return current_user_can( 'read' );
        },
        
        // Exponer automáticamente en la REST API para uso externo o IA
        'meta' => array(
            'show_in_rest' => true, 
        )
    ) );
}
```
### 3. Puntos Clave
Puntos clave:
• Namespacing: El nombre de la habilidad usa el formato namespace/nombre-habilidad (ej. mi-plugin/obtener-conteo-posts) para evitar conflictos.
• Esquemas (Schemas): Definen estrictamente los tipos de datos (string, integer) para validación automática,.
• show_in_rest: Al establecer esto en true, la habilidad se expone automáticamente en los endpoints de la API REST bajo wp-abilities/v1, facilitando su descubrimiento por herramientas de IA,.
3. Lógica de Ejecución (Callback)
Finalmente, definimos la función PHP que realiza el trabajo real. Esta función recibe la entrada validada según el esquema definido anteriormente.
```php
/**
 * Callback de ejecución para la habilidad obtener-conteo-posts.
 */
function mi_plugin_ejecutar_conteo( $input ) {
    // Si no se provee input, usa 'post' como default (definido en el esquema)
    $post_type = $input ?? 'post';
    
    // Lógica de negocio de WordPress
    $count = wp_count_posts( $post_type );
    
    return (int) $count->publish;
}
Cómo probar y ejecutar la habilidad
Una vez registrada, hay dos formas principales de utilizar esta habilidad:
A. Ejecución mediante PHP (Interna): Puedes invocar la habilidad programáticamente desde cualquier parte de tu código (por ejemplo, en un bloque o plantilla).
add_action( 'init', 'probar_mi_habilidad' );

function probar_mi_habilidad() {
    // 1. Obtener el objeto de la habilidad
    $habilidad = wp_get_ability( 'mi-plugin/obtener-conteo-posts' );
    
    // 2. Ejecutarla pasando el input deseado
    if ( $habilidad ) {
        $resultado = $habilidad->execute( 'page' ); // Contar páginas
        error_log( 'Número de páginas: ' . $resultado );
    }
}
```
B. Ejecución vía REST API (Externa/IA): Dado que activamos show_in_rest, puedes ejecutarla remotamente (requiere autenticación):
```bash
curl -u 'USUARIO:CONTRASEÑA_APLICACION' \
-X POST https://tu-sitio.com/wp-json/wp-abilities/v1/abilities/mi-plugin/obtener-conteo-posts/run \
-H "Content-Type: application/json" \
-d '{"input": "post"}'
```
Analogía para entender el concepto
Para consolidar lo aprendido, piensa en la Abilities API como el menú de un restaurante:
• La Habilidad registrada es un plato en el menú (con su descripción e ingredientes/schema).
• La Categoría es la sección del menú (Entradas, Platos Fuertes).
• El Callback es la receta que el chef ejecuta en la cocina cuando alguien pide ese plato.
• El Cliente puede ser un usuario humano (PHP) o un asistente de IA (vía REST) que lee el menú y hace el pedido.
Sin este menú (la API), el cliente (IA) tendría que entrar a la cocina y adivinar qué ingredientes hay disponibles, lo cual es ineficiente y propenso a errores.


## Explicación de los Cambios
### 1. Helper Function (weatherblock_fetch_from_api):

- ¿Por qué? Antes tenías la lógica wp_remote_get repetida en dos lugares. Para que la Abilities API funcione bien, necesita llamar a una función pura que devuelva datos sin preocuparse de si viene de un bloque o de una API REST.

- Esta función maneja la obtención de la API key, la llamada HTTP y el manejo de errores.

### 2. Registro de Categoría (weather-utilities):

- Según el whitepaper, debemos organizar nuestras habilidades. He creado una categoría para utilidades del clima.

### 3. Habilidades
- Habilidad 1: weatherblock/get-current-weather:

Propósito: Expone la capacidad principal de tu plugin (consultar clima) al sistema de habilidades.

Input Schema: Define que requiere un string para la ciudad.

Output Schema: Define que devolverá un objeto con temperatura, humedad, etc.

Utilidad: Un agente de IA ahora puede ejecutar esto para "ver" el clima sin tener que renderizar HTML.

- Habilidad 2: weatherblock/set-post-city:

Propósito: Permite la escritura. Modifica el post meta weatherblock_city_name.

Permisos: Fíjate en el permission_callback. Verifica current_user_can('edit_posts'). Además, dentro del callback de ejecución, verificamos si el usuario puede editar ese post específico. Esto es crucial para la seguridad.

Utilidad: Un agente de IA que esté redactando un artículo sobre "Viaje a París" podría invocar esta habilidad para configurar automáticamente el widget del clima del post a "París".