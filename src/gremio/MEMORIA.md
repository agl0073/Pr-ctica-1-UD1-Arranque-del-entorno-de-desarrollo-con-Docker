# Memoria · Gremio de Aventureros

**Código de alumno/a:** agl0073  (no pongas tu nombre)

## 1. Mi aventurero
- Usuario que he añadido a la tabla: `agl0073` (y comprobado con `brunhilda`, `aldric` y `vex`)
- Comando o pasos que he usado para generar el hash:
  Importación del script `gremio.sql` desde phpMyAdmin (o consola MySQL) con las contraseñas cifradas en bcrypt mediante `PASSWORD_DEFAULT` / `password_hash()`.

## 2. Parte 1 · match y estructura del tablón

### Decisión sobre `default` en `etiquetaRango()`
- **¿Se ha incluido `default`?** No, no se ha puesto cláusula `default`.
- **¿Qué ocurre si llega el rango `'Z'`?** Al evaluar una expresión `match` en PHP 8 sin una rama coincidente ni una rama `default`, el motor de PHP lanza de forma inmediata una excepción nativa de tipo `UnhandledMatchError`.
- **Justificación técnica:** Se prefiere deliberadamente este comportamiento estricto (*fail-fast*). El dominio de la aplicación define de forma cerrada que los únicos rangos válidos son los declarados en la constante `RANGOS` (`E`, `D`, `C`, `B`, `A`, `S`). Omitir el `default` evita enmascarar errores de integridad en los datos o fallos lógicos en fases tempranas. Si llegase un valor anómalo como `'Z'`, no se pintará una interfaz inconsistente ni se degradará silenciosamente el estado de la aplicación; en su lugar, se detectará de inmediato el fallo para corregirlo en su origen.

### Resumen de implementación técnica (Parte 1)
1. **`etiquetaRango(string $rango): string`**:
   - Mapeo unívoco y estricto mediante la expresión `match` de PHP 8 entre la letra del rango y su denominación canónica (`E` → Novato, `D` → Aprendiz, `C` → Veterano, `B` → Experto, `A` → Élite, `S` → Leyenda).
2. **`claseRango(string $rango): string`**:
   - Agrupación por ramas múltiples en `match` (`'E', 'D' => 'facil'`, `'C', 'B' => 'media'`, `'A', 'S' => 'dificil'`), vinculando la dificultad directamente con las clases semánticas de borde CSS definidas en `estilo.css`.
3. **`formatearOro(int $oro): string`**:
   - Formateo numérico mediante `number_format($oro, 0, ',', '.') . ' mo'`, garantizando el separador de miles estándar (punto) sin decimales y con la unidad monetaria requerida.
4. **Renderizado del tablón (`tablon.php`)**:
   - Control de colecciones vacías informando al usuario en caso de ausencia de misiones que cumplan los criterios.
   - Iteración de la colección mediante un bucle `foreach ($visibles as $mision)`.
   - Protección integral contra vulnerabilidades Cross-Site Scripting (XSS) aplicando de forma sistemática la función de escape `e()` (`htmlspecialchars`) sobre todos los campos procedentes de datos.
   - Manejo de concordancia gramatical para la duración (`1 día` en singular frente a `N días` en plural).
   - Renderizado condicional del botón de interacción según el estado de la misión (formulario POST con identificador oculto o marca visual `✔ En tu zurrón`).

## 3. Parte 2 · Validación de la URL

Al introducir manualmente en el navegador `tablon.php?rango=Z&oro=hola`, ocurre lo siguiente:

- **`?rango=Z`**: La función `leerRangoFiltro($_GET)` convierte el valor a mayúsculas con `strtoupper()` y lo comprueba contra la constante `RANGOS` usando `in_array($rango, RANGOS, true)`. Como `'Z'` no existe en ese array, la función devuelve `null`, lo que hace que `filtrarMisiones()` ignore el filtro de rango y muestre todas las misiones sin ningún error ni aviso.

- **`&oro=hola`**: La función `leerOroMinimo($_GET)` utiliza `filter_var($get['oro'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 10000]])`. La cadena `'hola'` no es un entero válido, por lo que `filter_var` devuelve `false` y la función retorna `0`. El filtro de oro mínimo queda en cero, mostrando todas las misiones sin errores.

**Resultado final**: se muestran las 10 misiones como si no se hubiese aplicado ningún filtro. No se produce ningún aviso de PHP, error visible ni comportamiento inesperado. El código defiende la aplicación de entradas malformadas o manipuladas en la URL, cumpliendo el principio de validar siempre los datos externos antes de usarlos.

## 4. Parte 3 · Sesión frente a cookie
- **Qué se guarda en `$_SESSION`:** Se guardan los datos sensibles y el estado del usuario conectado en el servidor: los datos del aventurero (`id`, `usuario`, `nombre`, `clase`), el array de misiones aceptadas en el zurrón (`$_SESSION['zurron']`), la hora de entrada y el contador de visitas a la sesión.
- **Qué se guarda en cookies:** Se guardan únicamente preferencias no críticas y datos del navegador del cliente: el tema visual (`gremio_tema`, con valores `claro` u `oscuro`) y el nombre de usuario recordado (`gremio_usuario`) si marcó la casilla.
- **¿Por qué no al revés?:** Las cookies se almacenan en el equipo del cliente y viajan en cada cabecera HTTP; el usuario las puede ver, modificar o manipular libremente desde las herramientas de desarrollador. Guardar datos sensibles (contraseñas, hashes, permisos o el contenido del zurrón) en cookies permitiría su alteración y comprometería la seguridad. En cambio, `$_SESSION` se almacena exclusivamente en la memoria del servidor bajo el identificador seguro `PHPSESSID`, garantizando la integridad de los datos.

## 5. Parte 3 · Seguridad del login
- **¿Qué pasa si en el usuario escribes `' OR '1'='1`? ¿Por qué?**  
  No ocurre absolutamente nada y la autenticación falla con el mensaje genérico de error. Se debe a que la consulta utiliza sentencias preparadas con PDO (`WHERE usuario = :usuario`). El motor de base de datos separa la lógica SQL de los datos, tratando la entrada maliciosa como una simple cadena de texto literal y neutralizando cualquier intento de inyección SQL.

- **¿Para qué sirve `session_regenerate_id(true)`?**  
  Sirve para prevenir ataques de **fijación de sesión** (*session fixation*). Al autenticar al usuario con éxito, se genera un identificador de sesión completamente nuevo y se invalida el anterior, impidiendo que un tercero que conozca el `PHPSESSID` previo pueda suplantar la sesión una vez que el usuario ha iniciado sesión.

- **¿Por qué el mensaje de error es el mismo si falla el usuario o la contraseña?**  
  Por motivos de seguridad contra **enumeración de usuarios**. Si se indicara «El usuario no existe» o «Contraseña incorrecta», un atacante podría deducir qué nombres de usuario están dados de alta en el sistema. Con un mensaje genérico único («Usuario o contraseña incorrectos»), no se revela ninguna información útil para posibles ataques por fuerza bruta o diccionario.

## 6. Pruebas que he hecho
| Prueba | Resultado esperado | Resultado obtenido |
|---|---|---|
| Entrar en tablon.php sin login | Redirige a `login.php?motivo=privado` con aviso de sala privada | Redirección correcta y aviso «Esa sala es solo para miembros del gremio» |
| Login con contraseña mala | Muestra mensaje de error genérico | Muestra «Usuario o contraseña incorrectos.» en rojo |
| Aceptar la misma misión dos veces | Mensaje flash de aviso sin duplicar en el zurrón | Avisa que ya está en el zurrón y mantiene 1 copia |
| Cerrar sesión y volver al zurrón | Destruye la sesión y redirige a login | Redirige a `login.php?motivo=salida` con zurrón vaciado |
| Cambiar el tema y cerrar sesión | El tema oscuro se mantiene gracias a la cookie | El tema oscuro persiste tras cerrar sesión y en toda la web |

## 7. Dificultades y cómo las he resuelto
- **Sintaxis y evaluación estricta en la expresión `match`:**  
  Al principio existía la tentación de replicar el comportamiento de un `switch` clásico añadiendo una rama por defecto (`default`) o usando sentencias `break`. Tras consultar la documentación de PHP 8, se comprendió que `match` devuelve directamente un valor como expresión y realiza comparaciones estrictas (`===`). La dificultad radicó en decidir el manejo de entradas no válidas: en lugar de devolver un string genérico que ocultara fallos, se optó conscientemente por omitir el `default` para aprovechar el lanzamiento automático de `UnhandledMatchError` como salvaguarda de integridad.

- **Mapeo múltiple a clases CSS:**  
  Para asociar dos rangos a una misma clase de estilo (como `E` y `D` a `facil`), surgieron dudas iniciales sobre si debían anidarse condiciones. Se resolvió de forma concisa aprovechando la sintaxis de comas por brazo permitida en `match` (`'E', 'D' => 'facil'`), logrando un código limpio y legible.

- **Concordancia gramatical y formateo numérico:**  
  Al iterar las tarjetas de misión, las duraciones de 1 día mostraban inicialmente «1 días». Se solventó implementando un operador ternario que evalúa si el valor es exactamente 1 (`$mision['dias'] === 1 ? '1 día' : $mision['dias'] . ' días'`). Asimismo, para la recompensa económica se ajustaron los parámetros de `number_format($oro, 0, ',', '.')` para evitar decimales innecesarios y emplear el punto como separador de millares estándar en España.

- **Conexión PDO en el entorno Docker y depuración del login:**  
  Al probar el inicio de sesión inicial, surgieron fallos al mapear los parámetros de conexión de MySQL entre las variables de entorno de Docker (`MYSQL_*`) y los puertos de acceso de Nginx y phpMyAdmin. Con ayuda de consultas de depuración guiadas, se ajustaron las variables en `docker-compose.yml` para garantizar que `conectar()` resolviese correctamente el host interno de base de datos.

## 8. Uso de Inteligencia Artificial
En esta práctica se ha hecho uso de herramientas de Inteligencia Artificial de forma puntual y como apoyo asistido:
1. **Asistencia técnica en el inicio de sesión:** Se consultó a la IA para orientar la resolución de problemas durante la configuración del entorno Docker (conexión PDO y arranque de phpMyAdmin) y para revisar la estructura del flujo de autenticación segura (`password_verify`, sentencias preparadas y regeneración de sesión), facilitando la comprensión y puesta en marcha del login sin errores de ejecución.
2. **Revisión documental:** La IA se utilizó como asistente de redacción para maquetar, estructurar y pulir la redacción formal de este archivo de memoria (`MEMORIA.md`). El análisis lógico de las soluciones y la integración práctica fueron guiados y comprobados manualmente por el alumno.
