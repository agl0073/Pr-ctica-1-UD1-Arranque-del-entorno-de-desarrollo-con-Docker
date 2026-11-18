# Memoria · Gremio de Aventureros

**Código de alumno/a:** agl0073  (no pongas tu nombre)

## 1. Mi aventurero
- Usuario que he añadido a la tabla: ______
- Comando o pasos que he usado para generar el hash:

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
Escribe a mano en la barra `tablon.php?rango=Z&oro=hola`. ¿Qué ocurre y qué línea de tu código lo evita?

## 4. Parte 3 · Sesión frente a cookie
Explica con tus palabras qué guardas en `$_SESSION` y qué guardas en cookies, y por qué no al revés.

## 5. Parte 3 · Seguridad del login
- ¿Qué pasa si en el usuario escribes `' OR '1'='1`? ¿Por qué?
- ¿Para qué sirve `session_regenerate_id(true)`?
- ¿Por qué el mensaje de error es el mismo si falla el usuario o la contraseña?

## 6. Pruebas que he hecho
| Prueba | Resultado esperado | Resultado obtenido |
|---|---|---|
| Entrar en tablon.php sin login | | |
| Login con contraseña mala | | |
| Aceptar la misma misión dos veces | | |
| Cerrar sesión y volver al zurrón | | |
| Cambiar el tema y cerrar sesión | | |

## 7. Dificultades y cómo las he resuelto
- **Sintaxis y evaluación estricta en la expresión `match`:**  
  Al principio existía la tentación de replicar el comportamiento de un `switch` clásico añadiendo una rama por defecto (`default`) o usando sentencias `break`. Tras consultar la documentación de PHP 8, se comprendió que `match` devuelve directamente un valor como expresión y realiza comparaciones estrictas (`===`). La dificultad radicó en decidir el manejo de entradas no válidas: en lugar de devolver un string genérico que ocultara fallos, se optó conscientemente por omitir el `default` para aprovechar el lanzamiento automático de `UnhandledMatchError` como salvaguarda de integridad.

- **Mapeo múltiple a clases CSS:**  
  Para asociar dos rangos a una misma clase de estilo (como `E` y `D` a `facil`), surgieron dudas iniciales sobre si debían anidarse condiciones. Se resolvió de forma concisa aprovechando la sintaxis de comas por brazo permitida en `match` (`'E', 'D' => 'facil'`), logrando un código limpio y legible.

- **Concordancia gramatical y formateo numérico:**  
  Al iterar las tarjetas de misión, las duraciones de 1 día mostraban inicialmente «1 días». Se solventó implementando un operador ternario que evalúa si el valor es exactamente 1 (`$mision['dias'] === 1 ? '1 día' : $mision['dias'] . ' días'`). Asimismo, para la recompensa económica se ajustaron los parámetros de `number_format($oro, 0, ',', '.')` para evitar decimales innecesarios y emplear el punto como separador de millares estándar en España.

- **Legibilidad en la interpolación y renderizado de HTML:**  
  La mezcla continua de etiquetas de apertura y cierre de PHP (`<?php` y `?>`) dentro de cada atributo HTML reducía la claridad del código y aumentaba el riesgo de introducir espacios o caracteres residuales. Se resolvió unificando la generación de las tarjetas mediante un único bloque de ejecución PHP estructurado, construyendo la salida con llamadas limpias a `echo` y protegiendo cada dato dinámico con `e()`.

## 8. Uso de Inteligencia Artificial
El único uso de herramientas de Inteligencia Artificial en esta práctica ha sido exclusivamente como apoyo para la redacción, corrección de estilo y estructuración documental de este archivo de memoria (`MEMORIA.md`). Todo el análisis lógico, diseño e implementación del código fuente del proyecto ha sido desarrollado de forma manual y autónoma conforme a los requisitos de la actividad.
