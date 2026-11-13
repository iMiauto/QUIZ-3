# Mutantes de la clase Grupo, corrida inicial

Corrida inicial de pruebas de mutación de Pest sobre `app/Models/Grupo.php`, hecha antes de agregar o cambiar cualquier prueba. La salida completa está en `entrega/mutacion-antes.txt`.

## Cómo se ejecutó

El comando fue `vendor\bin\pest --mutate --path=app/Models/Grupo.php`, con el PHP 8.3.35 de Laragon y PCOV 1.0.12 como driver de cobertura. El archivo `tests/Feature/MatriculaTest.php` declara `mutates(Grupo::class);` justo después de los `use`.

El comando simple `pest --mutate` no sirve en este equipo y devuelve "0 Mutations for 0 Files created". Sin la opción `--path`, Pest toma la ruta de `phpunit.xml` como una ruta absoluta de Windows y el buscador de archivos del plugin le antepone el directorio actual, por lo que nunca encuentra `Grupo.php`. Con una ruta relativa el problema desaparece. La opción `--path` se apunta al archivo y no a la carpeta porque con `app/Models` Pest mutó todos los modelos y no solo `Grupo`.

## Resumen

| Concepto | Cantidad |
|---|---|
| Total de mutantes generados | 86 |
| Tested (eliminados por alguna prueba) | 71 |
| Untested (cubiertos, pero ninguna prueba falló) | 0 |
| Uncovered (sin cobertura de líneas) | 15 |
| Puntaje de mutación | 82.56 % |

El puntaje sale de 71 mutantes eliminados entre 86 generados. Ningún mutante sobrevivió dentro de código ejecutado, pues los 15 que quedaron fuera están todos en la zona de declaraciones de la clase, entre las líneas 16 y 28.

## Por qué quedaron uncovered

Pest solo ejecuta pruebas contra un mutante si PCOV marca su línea como ejecutada. Las constantes de clase y el valor inicial de la propiedad `$fillable` se resuelven al compilar la clase y no generan instrucciones ejecutables, así que PCOV nunca las reporta como cubiertas, aunque todas las pruebas las usan de forma indirecta. Se confirmó con `--id` sobre los mutantes de las líneas 16 y 28, que terminaron igual como uncovered sin correr ninguna prueba.

Por eso la columna de explicación distingue dos cosas. La primera es la causa real de que el mutante figure como uncovered. La segunda es lo que probablemente pasaría si el mutante sí se ejecutara, deducido leyendo los casos CP-01 a CP-13. Esa segunda parte es un análisis y no un resultado medido.

## Mutantes uncovered

| ID del mutante | Línea de Grupo.php | Operador | Código original | Código mutado | Por qué ninguna prueba lo detectó |
|---|---|---|---|---|---|
| 8af245a74db8c525 | 16 | DecrementInteger | `NOTA_MINIMA = 0` | `NOTA_MINIMA = -1` | La línea de la constante no tiene instrucciones ejecutables, por lo que Pest no corre pruebas contra ella. Si se ejecutara, CP-12 con la nota -1 lo eliminaría, porque -1 ya no sería menor que el mínimo y no se lanzaría la excepción. |
| e039a88e0c27f398 | 16 | IncrementInteger | `NOTA_MINIMA = 0` | `NOTA_MINIMA = 1` | Misma causa de cobertura. Además es un hueco real de las pruebas, pues ningún caso registra la nota 0 como válida. CP-11 usa 8.5 y CP-12 usa -1, que sigue siendo menor que 1, por lo que probablemente sobreviviría. |
| 53045d883367a988 | 18 | DecrementInteger | `NOTA_MAXIMA = 10` | `NOTA_MAXIMA = 9` | La línea de la constante no es ejecutable. Si se ejecutara probablemente sobreviviría, porque ningún caso registra la nota 10 como válida. CP-11 usa 8.5, que sigue siendo menor que 9, y CP-12 usa 10.5, que sigue siendo mayor. |
| 6ab8b1a7e4db82bd | 18 | IncrementInteger | `NOTA_MAXIMA = 10` | `NOTA_MAXIMA = 11` | Misma causa de cobertura. Si se ejecutara, CP-12 con la nota 10.5 lo eliminaría, porque 10.5 ya no superaría el máximo y no se lanzaría la excepción. |
| 2b8c38a57c3f85c4 | 20 | DecrementInteger | `MIN_DOCENTES = 1` | `MIN_DOCENTES = 0` | La línea de la constante no es ejecutable. Si se ejecutara, CP-07 lo eliminaría, porque ese caso usa un grupo sin docentes y con 20 estudiantes, que pasaría a poder abrirse. |
| f19d1d4e76b56205 | 20 | IncrementInteger | `MIN_DOCENTES = 1` | `MIN_DOCENTES = 2` | Misma causa de cobertura. Si se ejecutara, CP-06 lo eliminaría, porque ese caso abre un grupo con un solo docente y pasaría a fallar. |
| d9c3bf43de197422 | 22 | DecrementInteger | `MAX_DOCENTES = 2` | `MAX_DOCENTES = 1` | La línea de la constante no es ejecutable. Si se ejecutara, CP-03 lo eliminaría, porque asigna dos docentes al mismo grupo y el segundo lanzaría la excepción. |
| 8efe655a8f013ed2 | 22 | IncrementInteger | `MAX_DOCENTES = 2` | `MAX_DOCENTES = 3` | Misma causa de cobertura. Si se ejecutara, CP-04 lo eliminaría, porque el tercer docente ya se aceptaría y no se lanzaría la excepción. |
| dcb9dd91aba02e66 | 24 | DecrementInteger | `MIN_ESTUDIANTES = 15` | `MIN_ESTUDIANTES = 14` | La línea de la constante no es ejecutable. Si se ejecutara, CP-05 lo eliminaría, porque el grupo con 14 estudiantes pasaría a poder abrirse. |
| ea37739af70405a6 | 24 | IncrementInteger | `MIN_ESTUDIANTES = 15` | `MIN_ESTUDIANTES = 16` | Misma causa de cobertura. Si se ejecutara, CP-06 lo eliminaría, porque el grupo con exactamente 15 estudiantes dejaría de poder abrirse. |
| ae29ba4aa46a143e | 28 | RemoveArrayItem | `['curso_id', 'numeroCiclo', 'ano', 'numeroGrupo', 'abierto']` | `['numeroCiclo', 'ano', 'numeroGrupo', 'abierto']` | El valor inicial de `$fillable` no es una línea ejecutable. Si se ejecutara, todas las pruebas que crean un grupo lo eliminarían, porque `curso_id` no se asignaría y la columna es obligatoria. |
| 562fb2a46ef37ad1 | 28 | RemoveArrayItem | `['curso_id', 'numeroCiclo', 'ano', 'numeroGrupo', 'abierto']` | `['curso_id', 'ano', 'numeroGrupo', 'abierto']` | Misma causa de cobertura. Si se ejecutara, CP-01 y las demás pruebas que crean un grupo lo eliminarían, porque `numeroCiclo` no se asignaría y la columna es obligatoria. |
| 2c71a84167290a5d | 28 | RemoveArrayItem | `['curso_id', 'numeroCiclo', 'ano', 'numeroGrupo', 'abierto']` | `['curso_id', 'numeroCiclo', 'numeroGrupo', 'abierto']` | Misma causa de cobertura. Si se ejecutara, CP-01 y las demás pruebas que crean un grupo lo eliminarían, porque `ano` no se asignaría y la columna es obligatoria. |
| 2b69222c7dc929f1 | 28 | RemoveArrayItem | `['curso_id', 'numeroCiclo', 'ano', 'numeroGrupo', 'abierto']` | `['curso_id', 'numeroCiclo', 'ano', 'abierto']` | Misma causa de cobertura. Si se ejecutara, CP-02 y las demás pruebas que crean un grupo lo eliminarían, porque `numeroGrupo` no se asignaría y la columna es obligatoria. |
| 547988442e59e4bf | 28 | RemoveArrayItem | `['curso_id', 'numeroCiclo', 'ano', 'numeroGrupo', 'abierto']` | `['curso_id', 'numeroCiclo', 'ano', 'numeroGrupo']` | Misma causa de cobertura. Si se ejecutara, CP-02, CP-05 y CP-07 lo eliminarían, porque crean grupos con `abierto` en falso y, sin ese campo asignable, la columna tomaría su valor por defecto en verdadero. |

Nota sobre las cinco filas de la línea 28. Pest no escribe en disco el código de los mutantes uncovered, así que no fue posible comprobar qué elemento quita cada uno. La asignación de elementos sigue el orden en que Pest los lista, de `curso_id` a `abierto`, y es una deducción. Por ese orden, la última fila corresponde a la eliminación de `abierto`.

## Hallazgos para el paso siguiente

- Ningún mutante sobrevivió en código ejecutado, por lo que las pruebas actuales eliminan todo lo que Pest logra ejecutar.
- Los 15 uncovered se concentran en constantes y en `$fillable`. Se consideran artefactos de cobertura de líneas y no ausencia de pruebas, salvo los dos casos de frontera siguientes.
- Las constantes `NOTA_MINIMA` y `NOTA_MAXIMA` tienen un hueco real, pues ninguna prueba usa las notas 0 y 10, que son válidas. Los mutantes e039a88e0c27f398 y 53045d883367a988 sobrevivirían si se ejecutaran.

## Mutantes eliminados en C2, tres ejemplos revisados con --id

La salida de Pest no muestra el ID de los mutantes eliminados. Los tres IDs siguientes se calcularon con la misma fórmula del plugin, que aplica un hash xxh3 a la ruta del archivo, el operador y el código mutado, y se comprobaron con `--id`. En los tres casos la corrida devolvió un solo mutante tested y un puntaje de 100 %. Pest tampoco indica qué prueba eliminó cada mutante, por lo que se aplicó el mismo cambio a mano en `Grupo.php` y se anotaron las pruebas que fallaron.

| ID del mutante | Línea | Operador | Código original | Código mutado | Prueba que lo elimina |
|---|---|---|---|---|---|
| 617b7f31f59e7a5f | 110 | GreaterOrEqualToGreater | `$estudiantes >= self::MIN_ESTUDIANTES` | `$estudiantes > self::MIN_ESTUDIANTES` | CP-06 |
| f4879b4ef80f0433 | 71 | GreaterOrEqualToGreater | `count() >= self::MAX_DOCENTES` | `count() > self::MAX_DOCENTES` | CP-04 |
| cef55dafa1e7e9fd | 87 | BooleanOrToBooleanAnd | `$nota < self::NOTA_MINIMA \|\| $nota > self::NOTA_MAXIMA` | `$nota < self::NOTA_MINIMA && $nota > self::NOTA_MAXIMA` | CP-12 |

### 617b7f31f59e7a5f, eliminado por CP-06

El cambio de `>=` por `>` en `puedeAbrirse` hace que se exijan más de 15 estudiantes en lugar de al menos 15. CP-06 matricula exactamente 15 estudiantes y espera que `puedeAbrirse()` devuelva verdadero y que `abrir()` deje el grupo abierto. Con el mutante, 15 ya no alcanza, `puedeAbrirse()` devuelve falso y la prueba falla. CP-05 no detecta este mutante porque con 14 estudiantes tanto la condición original como la mutada dan falso.

### f4879b4ef80f0433, eliminado por CP-04

El cambio de `>=` por `>` en `asignarDocente` hace que el grupo acepte un tercer docente, ya que con dos docentes asignados la comparación 2 > 2 es falsa. CP-04 asigna dos docentes, intenta un tercero y espera una `DomainException`. Con el mutante no se lanza ninguna excepción y la prueba falla en la llamada a `fail()`. CP-03 no lo detecta porque solo asigna dos docentes y nunca llega al límite.

### cef55dafa1e7e9fd, eliminado por CP-12

El cambio de `||` por `&&` en `registrarNota` exige que la nota sea menor que 0 y mayor que 10 al mismo tiempo, algo imposible, de modo que ninguna nota se rechaza. CP-12 prueba los valores -1 y 10.5 y espera una `InvalidArgumentException` en ambos. Con el mutante no se lanza la excepción y fallan los dos datasets. CP-11 no lo detecta porque solo registra una nota válida de 8.5.

## Después de C3

### Pruebas agregadas

Se agregaron dos pruebas a `tests/Feature/MatriculaTest.php`, sin modificar `Grupo.php` ni los casos CP-01 a CP-13.

- **C3 acepta las notas límite 0 y 10.** Usa un dataset con los valores 0 y 10, matricula a C12345 en el grupo 01, registra la nota y verifica que la nota guardada es igual al valor, que es de tipo float y que la fila de la base de datos contiene ese valor.
- **C3 rechaza registrar nota a un estudiante no matriculado.** Ningún caso anterior probaba directamente esta rama de `registrarNota`. La prueba espera una `DomainException` y verifica que no se creó ninguna fila en `estudiantes_matriculados`.

### Mutación manual de los 15 uncovered

Cada cambio se aplicó a mano en `Grupo.php`, se corrió `php artisan test` y se restauró el archivo desde una copia de respaldo. No se usó `git checkout` porque `Grupo.php` tenía cambios sin confirmar y ese comando los habría descartado. Tras cada mutante y al final se comparó el hash SHA-256 del archivo con el original, y coincidió siempre. Antes de C3 la suite tenía 21 pruebas y después tiene 24.

| ID | Constante o elemento | Cambio aplicado | Resultado antes de C3 | Resultado después de C3 (prueba que lo detecta) |
|---|---|---|---|---|
| 8af245a74db8c525 | `NOTA_MINIMA` (línea 16) | 0 pasa a -1 | Detectado, falla CP-12 | Detectado, falla CP-12 con el valor -1 |
| e039a88e0c27f398 | `NOTA_MINIMA` (línea 16) | 0 pasa a 1 | No detectado, pasan 21 de 21 | Detectado, falla C3 con el valor 0 |
| 53045d883367a988 | `NOTA_MAXIMA` (línea 18) | 10 pasa a 9 | No detectado, pasan 21 de 21 | Detectado, falla C3 con el valor 10 |
| 6ab8b1a7e4db82bd | `NOTA_MAXIMA` (línea 18) | 10 pasa a 11 | Detectado, falla CP-12 | Detectado, falla CP-12 con el valor 10.5 |
| 2b8c38a57c3f85c4 | `MIN_DOCENTES` (línea 20) | 1 pasa a 0 | Detectado, falla CP-07 | Detectado, falla CP-07 |
| f19d1d4e76b56205 | `MIN_DOCENTES` (línea 20) | 1 pasa a 2 | Detectado, falla CP-06 | Detectado, falla CP-06 |
| d9c3bf43de197422 | `MAX_DOCENTES` (línea 22) | 2 pasa a 1 | Detectado, fallan CP-03 y las dos pruebas de CP-04 | Detectado, fallan CP-03 y las dos pruebas de CP-04 |
| 8efe655a8f013ed2 | `MAX_DOCENTES` (línea 22) | 2 pasa a 3 | Detectado, falla CP-04 | Detectado, falla CP-04 |
| dcb9dd91aba02e66 | `MIN_ESTUDIANTES` (línea 24) | 15 pasa a 14 | Detectado, falla CP-05 | Detectado, falla CP-05 |
| ea37739af70405a6 | `MIN_ESTUDIANTES` (línea 24) | 15 pasa a 16 | Detectado, falla CP-06 | Detectado, falla CP-06 |
| ae29ba4aa46a143e | `curso_id` en `$fillable` (línea 28) | Se quita el elemento | Detectado, fallan 13 pruebas de CP-01 a CP-13 | Detectado, fallan esas 13 pruebas y las 3 de C3 |
| 562fb2a46ef37ad1 | `numeroCiclo` en `$fillable` (línea 28) | Se quita el elemento | Detectado, fallan 13 pruebas de CP-01 a CP-13 | Detectado, fallan esas 13 pruebas y las 3 de C3 |
| 2c71a84167290a5d | `ano` en `$fillable` (línea 28) | Se quita el elemento | Detectado, fallan 13 pruebas de CP-01 a CP-13 | Detectado, fallan esas 13 pruebas y las 3 de C3 |
| 2b69222c7dc929f1 | `numeroGrupo` en `$fillable` (línea 28) | Se quita el elemento | Detectado, fallan 13 pruebas de CP-01 a CP-13 | Detectado, fallan esas 13 pruebas y las 3 de C3 |
| 547988442e59e4bf | `abierto` en `$fillable` (línea 28) | Se quita el elemento | Detectado, fallan CP-02, CP-05 y CP-07 | Detectado, fallan CP-02, CP-05 y CP-07 |

Las 13 pruebas que fallan al quitar uno de los cuatro primeros elementos de `$fillable` son CP-01, CP-02, CP-03, las dos de CP-04, CP-05, CP-06, CP-07, CP-10, CP-11, las dos de CP-12 y CP-13. Los cinco mutantes de `$fillable` se asociaron a cada elemento según el orden en que Pest los lista, de `curso_id` a `abierto`. Esa asociación es una deducción, pero no cambia la conclusión porque en todos los casos la suite detecta el cambio.

Antes de C3 solo `NOTA_MINIMA = 1` y `NOTA_MAXIMA = 9` pasaban todas las pruebas, que es el hueco que se había detectado. Después de C3 los 15 cambios son detectados.

## Resultado de la corrida final

| Corrida | Comando | Mutantes | Tested | Untested | Uncovered | Puntaje |
|---|---|---|---|---|---|---|
| Inicial | `pest --mutate --path=app/Models/Grupo.php` | 86 | 71 | 0 | 15 | 82.56 % |
| Después de C3 con `--min=80` | `pest --mutate --path=app/Models/Grupo.php --min=80` | 86 | 71 | 0 | 15 | 82.56 % |
| Después de C3 con `--covered-only` | `pest --mutate --path=app/Models/Grupo.php --covered-only` | 71 | 71 | 0 | 0 | 100.00 % |

La corrida con `--min=80` terminó con código de salida 0, porque 82.56 % supera el mínimo. Las salidas completas están en `entrega/mutacion-despues.txt` y `entrega/mutacion-covered-only.txt`.

## Por qué el puntaje sigue en 82.56 %

Las dos pruebas de C3 detectan los 15 cambios de la tabla anterior, pero el puntaje de Pest no se movió. La herramienta decide qué mutantes ejecutar a partir de la cobertura de líneas que reporta PCOV. Las constantes de clase y el valor inicial de `$fillable` se resuelven al compilar la clase y no producen instrucciones ejecutables, por lo que PCOV no las marca como cubiertas. Pest clasifica esos mutantes como uncovered y nunca corre las pruebas contra ellos, sin importar cuántas pruebas los ejerciten de forma indirecta.

Por lo tanto, los 15 uncovered se justifican como no eliminables por la herramienta y no como señal de pruebas débiles. La mutación manual lo respalda, ya que todos los cambios hacen fallar la suite. No se usó `@pest-mutate-ignore`, de modo que los 15 mutantes siguen visibles en el informe y en el puntaje.

Como dato complementario, la corrida con `--covered-only` excluye las líneas no ejecutables y deja 71 mutantes, todos eliminados, con un puntaje de 100 %. Esa cifra describe la calidad de las pruebas sobre el código que Pest sí puede ejecutar, mientras que el 82.56 % incluye el costo de las líneas que la herramienta no puede medir.
