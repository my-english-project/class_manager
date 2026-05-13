   
**UNIVERSIDAD TECNOLÓGICA DE SALAMANCA**

Departamento de Tecnologías de la Información

 

**ClassHub**

Sistema de Administración de Clase

 

Especificación de Requerimientos de Software (ERS)

 

| Proyecto: | ClassHub – Sistema de Administración de Clase |
| :---- | :---- |
| **Institución:** | Universidad Tecnológica de Salamanca (UTS) |
| **Versión:** | 1.0.0 |
| **Fecha:** | Abril 2026 |
| **Tipo de documento:** | Especificación de Requerimientos de Software (ERS) |
| **Clasificación:** | Uso interno – Confidencial |

 

Versión 1.0  |  Abril 2026  |  Confidencial

# **1\. Introducción**

## **1.1 Propósito del Documento**

Este documento constituye la Especificación de Requerimientos de Software (ERS) para ClassHub, el Sistema de Administración de Clase de la Universidad Tecnológica de Salamanca (UTS). Define de manera exhaustiva los requerimientos funcionales y no funcionales, el modelo de datos, los casos de uso y las restricciones de diseño que deben guiar el desarrollo, prueba e implantación del sistema.

Está dirigido a: desarrolladores de software, arquitectos de sistemas, responsables de QA, administradores de la UTS y docentes participantes en el proceso de validación.

 

## **1.2 Alcance del Sistema**

ClassHub es una aplicación web que permite a los docentes de la UTS gestionar de forma integral sus grupos, capturar evaluaciones, tomar asistencia y consultar reportes; al mismo tiempo expone a los alumnos un portal de consulta donde pueden revisar sus calificaciones y asistencias en tiempo real.

El sistema cubre el modelo de evaluación por competencias (Ser, Saber, Hacer) establecido por la institución para la materia de Inglés, aplicable a periodos cuatrimestrales (ene-abr, may-ago, sep-dic).

 

## **1.3 Definiciones, Siglas y Acrónimos**

| Término / Sigla | Definición |
| :---: | ----- |
| UTS | Universidad Tecnológica de Salamanca |
| ERS | Especificación de Requerimientos de Software |
| RF | Requerimiento Funcional |
| RNF | Requerimiento No Funcional |
| WE | Write Exam – Examen Escrito |
| OE | Oral Exam – Examen Oral |
| PF | Portfolio – Portafolio de Evidencias |
| HW | Homework – Tarea |
| AT | Attendance – Asistencia |
| EX | Exam – Vista resumen de calificaciones por parcial |
| SITO | Vista de calificaciones finales del sistema institucional |
| HM | Home – Vista principal del sistema |
| BD | Base de Datos |
| CRUD | Create, Read, Update, Delete |
| Ciclo | Código alfanumérico de periodo: p.ej. 26C1 |

 

## **1.4 Referencias**

•   	Reglamento de Evaluación por Competencias – UTS, 2025\.

•   	IEEE Std 830-1998: Recommended Practice for Software Requirements Specifications.

•   	Guías de programación cuatrimestral UTS – Área de Inglés.

 

## **1.5 Resumen del Documento**

El documento se organiza en las siguientes secciones: (1) Introducción, (2) Descripción General, (3) Requerimientos Funcionales, (4) Requerimientos No Funcionales, (5) Modelo de Base de Datos, (6) Casos de Uso, (7) Reglas de Negocio, (8) Restricciones y (9) Apéndices.

 

# **2\. Descripción General del Sistema**

## **2.1 Perspectiva del Producto**

ClassHub opera como sistema web autónomo alojado en los servidores de la UTS. Se accede desde cualquier navegador moderno sin instalación local. El sistema se integra opcionalmente con el sistema institucional SITO para exportar calificaciones finales.

 

## **2.2 Funciones Principales**

•   	Autenticación de docentes y alumnos.

•   	Administración de grupos: alta, edición y consulta.

•   	Alta y gestión de alumnos por grupo.

•   	Registro de días de clase (calendario de sesiones).

•   	Toma de asistencia con estados diferenciados (asistencia, retardo, falta, justificado).

•   	Captura de calificaciones: Examen Escrito (WE), Examen Oral (OE), Portafolio (PF), Tareas (HW).

•   	Cálculo automático de calificaciones parciales y finales con ponderaciones institucionales.

•   	Vista resumen (EX) y vista SITO por grupo y periodo.

•   	Portal de consulta para alumnos.

•   	Indicadores estadísticos en el dashboard (Home).

 

## **2.3 Roles de Usuario**

| Rol | Descripción |
| :---: | ----- |
| Docente | Usuario principal. Gestiona grupos, alumnos, sesiones, asistencias y calificaciones. |
| Alumno | Usuario de consulta. Visualiza sus asistencias y calificaciones sin poder editarlas. |
| Administrador | Perfil futuro para gestión de usuarios y configuración institucional (fuera de alcance v1.0). |

 

## **2.4 Modelo de Evaluación por Competencias**

La UTS evalúa a través de tres competencias. Los porcentajes a continuación corresponden a la materia de Inglés:

 

| Competencia | Peso Global | Rubro | Descripción |
| :---: | :---: | :---: | ----- |
| Saber | 30 % | — | Examen Escrito (WE) |
| Hacer | 60 % | Aplicación (60 %) | Examen Oral (OE) |
|   |   | Portafolio (40 %) | Actividades del libro \+ Tareas (HW) |
| Ser | 10 % | — | Asistencia a clases (AT) |

 

## **2.5 Codificación de Ciclo Escolar**

El campo ciclo se genera automáticamente con el formato: \[AA\]\[C\]\[N\], donde:

•   	AA: dos últimos dígitos del año (p.ej. 26 para 2026).

•   	C: carácter fijo 'C' (cuatrimestral).

•   	N: número de periodo — 1 \= ene-abr, 2 \= may-ago, 3 \= sep-dic.

Ejemplo: el periodo mayo-agosto de 2026 genera el código 26C2.

 

# **3\. Requerimientos Funcionales**

## **3.1 Módulo de Autenticación**

| ID | Descripción del Requerimiento | Prioridad |
| :---: | ----- | :---: |
| RF-01 | El sistema presentará una pantalla de inicio de sesión con un botón único de acceso sin solicitar credenciales durante la fase de prototipo (v1.0). Se cargará automáticamente el perfil del docente de ejemplo precargado. | Alta |
| RF-02 | En futuras versiones el sistema validará usuario y contraseña contra la base de datos. La sesión caducará tras 30 minutos de inactividad. | Media |
| RF-03 | El sistema cargará el perfil del alumno al ingresar su matrícula en el portal de consulta. | Alta |

 

## **3.2 Módulo de Grupos**

| ID | Descripción del Requerimiento | Prioridad |
| :---: | ----- | :---: |
| RF-04 | El docente podrá registrar un grupo indicando: Carrera, Siglas de materia, Cuatrimestre, Identificador de Grupo, Periodo (ene-abr / may-ago / sep-dic). El sistema generará automáticamente el campo Ciclo. | Alta |
| RF-05 | El docente podrá consultar el listado de todos sus grupos con filtro por ciclo y carrera. | Alta |
| RF-06 | Ninguna vista del sistema mostrará datos de alumnos hasta que el docente seleccione un grupo activo. | Alta |
| RF-07 | El docente podrá editar los datos generales de un grupo siempre que no exista calificación capturada. Una vez iniciada la captura, solo se permitirá editar campos descriptivos. | Media |

 

## **3.3 Módulo de Alumnos**

| ID | Descripción del Requerimiento | Prioridad |
| :---: | ----- | :---: |
| RF-08 | El docente podrá dar de alta alumnos en un grupo indicando: Matrícula, Nombre(s), Apellido Paterno y Apellido Materno. | Alta |
| RF-09 | En todas las vistas el nombre del alumno se mostrará como: Apellido Paterno \+ Apellido Materno \+ Nombre(s). | Alta |
| RF-10 | El docente podrá eliminar a un alumno del grupo siempre que no tenga calificaciones registradas. | Media |
| RF-11 | El sistema validará que la matrícula sea única dentro de un grupo. | Alta |

 

## **3.4 Módulo de Sesiones (Días de Clase)**

| ID | Descripción del Requerimiento | Prioridad |
| :---: | ----- | :---: |
| RF-12 | El docente podrá registrar los días en que se impartirá clase para un grupo mediante un selector de fecha interactivo. | Alta |
| RF-13 | No se permitirá registrar fechas duplicadas para el mismo grupo. | Alta |
| RF-14 | El docente podrá eliminar una fecha de clase siempre que no exista asistencia registrada para esa sesión. | Media |

 

## **3.5 Vista Home (HM)**

| ID | Descripción del Requerimiento | Prioridad |
| :---: | ----- | :---: |
| RF-15 | La vista Home mostrará indicadores estadísticos: número total de grupos activos, número total de alumnos, porcentaje promedio de asistencia, distribución de calificaciones (aprobados/reprobados). | Alta |
| RF-16 | La vista Home mostrará una tabla con las Columnas Generales (Matrícula y Nombre completo) y las calificaciones de los parciales concluidos, más la columna de promedio general. | Alta |
| RF-17 | Todas las columnas de la tabla permitirán ordenamiento ascendente y descendente al hacer clic en el encabezado. | Alta |

 

## **3.6 Vista Attendance (AT)**

| ID | Descripción del Requerimiento | Prioridad |
| :---: | ----- | :---: |
| RF-18 | La vista AT mostrará las Columnas Generales y una columna por cada fecha de clase registrada por el docente para el grupo seleccionado. | Alta |
| RF-19 | Cada celda de asistencia es un control de estado cíclico con cuatro estados: 1 clic \= Verde (Asistencia), 2 clics \= Amarillo (Retardo), 3 clics \= Rojo (Falta), 4 clics \= Naranja (Justificado). | Alta |
| RF-20 | La vista contará con un botón 'Tomar Asistencia' que inicializará el estado Asistencia para todos los alumnos en la sesión seleccionada. | Alta |
| RF-21 | Se mostrará una leyenda visual con los cuatro estados y sus colores correspondientes. | Alta |
| RF-22 | La calificación de Ser se calculará automáticamente a partir del porcentaje de asistencias sobre el total de sesiones. | Alta |

 

## **3.7 Vista Write Exam (WE)**

| ID | Descripción del Requerimiento | Prioridad |
| :---: | ----- | :---: |
| RF-23 | La vista WE mostrará las Columnas Generales y tres columnas de captura correspondientes a los tres parciales. | Alta |
| RF-24 | El rango válido de calificación es 0.00 a 10.00. Cualquier valor por debajo de 7.00 se resaltará en rojo. | Alta |
| RF-25 | El sistema calculará el promedio del Examen Escrito con dos decimales. | Alta |

 

## **3.8 Vista Oral Exam (OE)**

| ID | Descripción del Requerimiento | Prioridad |
| :---: | ----- | :---: |
| RF-26 | La vista OE mostrará las Columnas Generales y tres columnas de captura para los tres parciales del Examen Oral. | Alta |
| RF-27 | El rango válido de calificación es 0.00 a 10.00. Cualquier valor por debajo de 7.00 se resaltará en rojo. | Alta |

 

## **3.9 Vista Portfolio (PF)**

| ID | Descripción del Requerimiento | Prioridad |
| :---: | ----- | :---: |
| RF-28 | La vista PF mostrará las Columnas Generales y permitirá al docente agregar dinámicamente columnas para registrar actividades de portafolio (libro o tarea). | Alta |
| RF-29 | Al agregar una columna, el docente asignará un nombre o número de actividad. | Media |
| RF-30 | La última columna será fija y mostrará el promedio de todas las actividades de portafolio registradas. | Alta |
| RF-31 | El docente podrá eliminar una columna de actividad siempre que no tenga calificaciones capturadas. | Media |

 

## **3.10 Vista Homework (HW)**

| ID | Descripción del Requerimiento | Prioridad |
| :---: | ----- | :---: |
| RF-32 | La vista HW mostrará las Columnas Generales y permitirá al docente agregar dinámicamente columnas para registrar tareas. | Alta |
| RF-33 | La última columna será fija y mostrará el promedio de todas las tareas registradas. | Alta |

 

## **3.11 Vista Exam (EX)**

| ID | Descripción del Requerimiento | Prioridad |
| :---: | ----- | :---: |
| RF-34 | La vista EX mostrará las Columnas Generales y una tabla resumen por parcial con los sub-totales ponderados de WE, OE, PF y AT, siguiendo el diseño de referencia (imagen1.png). | Alta |
| RF-35 | El sistema calculará automáticamente la calificación parcial con base en las ponderaciones: WE 30 %, OE 36 % (60 % de Hacer), PF 24 % (40 % de Hacer), AT 10 %. | Alta |
| RF-36 | Se mostrará la calificación final como promedio de los tres parciales. | Alta |

 

## **3.12 Vista SITO (ST)**

| ID | Descripción del Requerimiento | Prioridad |
| :---: | ----- | :---: |
| RF-37 | La vista SITO mostrará las Columnas Generales y las calificaciones finales desglosadas por competencia (Ser, Saber, Hacer) con el formato del sistema institucional, siguiendo el diseño de referencia (imagen2.png). | Alta |
| RF-38 | El formato de exportación para el sistema SITO será una tabla imprimible y opcionalmente exportable a CSV. | Media |

 

## **3.13 Guardado y Alertas**

| ID | Descripción del Requerimiento | Prioridad |
| :---: | ----- | :---: |
| RF-39 | Cada vista contará con un botón 'Guardar' claramente identificado. | Alta |
| RF-40 | El sistema emitirá una alerta de confirmación si el usuario intenta navegar a otra vista con cambios sin guardar. | Alta |
| RF-41 | El sistema mostrará notificaciones de éxito o error al guardar datos. | Alta |

 

# **4\. Requerimientos No Funcionales**

| ID | Descripción del Requerimiento | Prioridad |
| :---: | ----- | :---: |
| RNF-01 | Usabilidad: La interfaz deberá ser intuitiva, con tiempo de aprendizaje inferior a 30 minutos para un docente sin formación técnica. | Alta |
| RNF-02 | Rendimiento: El tiempo de carga de cualquier vista no excederá 2 segundos con hasta 50 alumnos por grupo en condiciones normales de red institucional. | Alta |
| RNF-03 | Compatibilidad: El sistema será compatible con los navegadores Chrome 110+, Firefox 110+, Edge 110+ y Safari 16+ en desktop y dispositivos móviles. | Alta |
| RNF-04 | Seguridad: Las contraseñas se almacenarán con hash bcrypt. Las sesiones se gestionarán con tokens JWT con expiración de 30 minutos. | Alta |
| RNF-05 | Disponibilidad: El sistema deberá estar disponible el 99 % del tiempo durante periodos escolares activos (lunes a viernes, 7:00–22:00 hrs). | Media |
| RNF-06 | Escalabilidad: La arquitectura deberá soportar al menos 200 usuarios concurrentes sin degradación visible del rendimiento. | Media |
| RNF-07 | Mantenibilidad: El código fuente seguirá el patrón MVC y contará con cobertura de pruebas unitarias de al menos 70 %. | Media |
| RNF-08 | Accesibilidad: El diseño seguirá las pautas WCAG 2.1 nivel AA. | Media |
| RNF-09 | Internacionalización: La interfaz docente se presentará en español; los identificadores de vistas (WE, OE, PF, HW, AT, EX, SITO) se mantendrán en inglés por convención institucional. | Baja |
| RNF-10 | Precisión numérica: Todas las calificaciones se almacenarán y presentarán con exactamente dos decimales. El valor mínimo aprobatorio es 7.00; cualquier valor inferior se marcará visualmente en rojo. | Alta |

 

# **5\. Modelo de Base de Datos**

Se emplea un modelo relacional. Los nombres de tablas están en singular y en minúsculas. La clave primaria de cada tabla sigue el patrón id\_{nombre\_tabla}.

 

## **5.1 Tabla: docente**

| Campo | Tipo | Restricciones | Descripción |
| ----- | ----- | ----- | ----- |
| id\_docente | INT PK AI | NOT NULL | Identificador único del docente. |
| nombre | VARCHAR(80) | NOT NULL | Nombre(s) del docente. |
| apellido\_pat | VARCHAR(60) | NOT NULL | Apellido paterno. |
| apellido\_mat | VARCHAR(60) |   | Apellido materno. |
| email | VARCHAR(120) | UNIQUE | Correo electrónico institucional. |
| password\_hash | VARCHAR(255) | NOT NULL | Hash bcrypt de la contraseña. |
| activo | TINYINT(1) | DEFAULT 1 | 1 \= activo, 0 \= inactivo. |
| created\_at | DATETIME | DEFAULT NOW | Fecha de creación del registro. |

 

## **5.2 Tabla: grupo**

| Campo | Tipo | Restricciones | Descripción |
| ----- | ----- | ----- | ----- |
| id\_grupo | INT PK AI | NOT NULL | Identificador único del grupo. |
| id\_docente | INT FK | NOT NULL | Referencia al docente propietario. |
| carrera | VARCHAR(120) | NOT NULL | Nombre completo de la carrera. |
| siglas | VARCHAR(20) | NOT NULL | Siglas de la materia (p.ej. ING4A). |
| cuatrimestre | TINYINT | NOT NULL | Número de cuatrimestre (1–10). |
| grupo | VARCHAR(10) | NOT NULL | Identificador de grupo (A, B, C…). |
| periodo | ENUM | NOT NULL | 'ene-abr' | 'may-ago' | 'sep-dic'. |
| ciclo | VARCHAR(6) | NOT NULL | Código generado: 26C1, 26C2, 26C3. |
| anio | SMALLINT | NOT NULL | Año del ciclo escolar (p.ej. 2026). |
| activo | TINYINT(1) | DEFAULT 1 | Estado del grupo. |
| created\_at | DATETIME | DEFAULT NOW | Fecha de creación. |

 

## **5.3 Tabla: alumno**

| Campo | Tipo | Restricciones | Descripción |
| ----- | ----- | ----- | ----- |
| id\_alumno | INT PK AI | NOT NULL | Identificador único del alumno. |
| matricula | VARCHAR(20) | NOT NULL | Matrícula institucional del alumno. |
| nombre | VARCHAR(80) | NOT NULL | Nombre(s) del alumno. |
| apellido\_pat | VARCHAR(60) | NOT NULL | Apellido paterno. |
| apellido\_mat | VARCHAR(60) |   | Apellido materno. |
| email | VARCHAR(120) |   | Correo del alumno (opcional). |
| activo | TINYINT(1) | DEFAULT 1 | Estado del alumno. |
| created\_at | DATETIME | DEFAULT NOW | Fecha de registro. |

 

## **5.4 Tabla: grupo\_alumno**

Tabla de relación muchos-a-muchos entre grupo y alumno (un alumno puede pertenecer a varios grupos).

| Campo | Tipo | Restricciones | Descripción |
| ----- | ----- | ----- | ----- |
| id\_grupo\_alumno | INT PK AI | NOT NULL | Identificador de la relación. |
| id\_grupo | INT FK | NOT NULL | Referencia al grupo. |
| id\_alumno | INT FK | NOT NULL | Referencia al alumno. |
| created\_at | DATETIME | DEFAULT NOW | Fecha de inscripción. |

 

## **5.5 Tabla: sesion**

| Campo | Tipo | Restricciones | Descripción |
| ----- | ----- | ----- | ----- |
| id\_sesion | INT PK AI | NOT NULL | Identificador único de la sesión. |
| id\_grupo | INT FK | NOT NULL | Grupo al que pertenece la sesión. |
| fecha | DATE | NOT NULL | Fecha de la clase. |
| parcial | TINYINT | NOT NULL | Número de parcial (1, 2 o 3). |
| created\_at | DATETIME | DEFAULT NOW | Fecha de registro. |

 

## **5.6 Tabla: asistencia**

| Campo | Tipo | Restricciones | Descripción |
| ----- | ----- | ----- | ----- |
| id\_asistencia | INT PK AI | NOT NULL | Identificador del registro. |
| id\_sesion | INT FK | NOT NULL | Sesión a la que corresponde. |
| id\_alumno | INT FK | NOT NULL | Alumno evaluado. |
| estado | ENUM | NOT NULL | 'asistencia' | 'retardo' | 'falta' | 'justificado'. |
| updated\_at | DATETIME |   | Última modificación. |

 

## **5.7 Tabla: examen\_escrito**

| Campo | Tipo | Restricciones | Descripción |
| ----- | ----- | ----- | ----- |
| id\_examen\_escrito | INT PK AI | NOT NULL | Identificador del registro. |
| id\_grupo | INT FK | NOT NULL | Grupo al que pertenece. |
| id\_alumno | INT FK | NOT NULL | Alumno evaluado. |
| parcial | TINYINT | NOT NULL | Número de parcial (1, 2 o 3). |
| calificacion | DECIMAL(4,2) |   | Calificación del examen escrito (0.00–10.00). |
| updated\_at | DATETIME |   | Última modificación. |

 

## **5.8 Tabla: examen\_oral**

| Campo | Tipo | Restricciones | Descripción |
| ----- | ----- | ----- | ----- |
| id\_examen\_oral | INT PK AI | NOT NULL | Identificador del registro. |
| id\_grupo | INT FK | NOT NULL | Grupo al que pertenece. |
| id\_alumno | INT FK | NOT NULL | Alumno evaluado. |
| parcial | TINYINT | NOT NULL | Número de parcial (1, 2 o 3). |
| calificacion | DECIMAL(4,2) |   | Calificación del examen oral (0.00–10.00). |
| updated\_at | DATETIME |   | Última modificación. |

 

## **5.9 Tabla: actividad**

Catálogo de actividades de portafolio y tareas para un grupo y parcial determinados.

| Campo | Tipo | Restricciones | Descripción |
| ----- | ----- | ----- | ----- |
| id\_actividad | INT PK AI | NOT NULL | Identificador único. |
| id\_grupo | INT FK | NOT NULL | Grupo al que pertenece. |
| tipo | ENUM | NOT NULL | 'portafolio' | 'tarea'. |
| parcial | TINYINT | NOT NULL | Número de parcial (1, 2 o 3). |
| nombre | VARCHAR(80) | NOT NULL | Nombre o número de la actividad. |
| orden | TINYINT |   | Orden de presentación en la tabla. |
| created\_at | DATETIME | DEFAULT NOW | Fecha de creación. |

 

## **5.10 Tabla: calificacion\_actividad**

| Campo | Tipo | Restricciones | Descripción |
| ----- | ----- | ----- | ----- |
| id\_calificacion\_actividad | INT PK AI | NOT NULL | Identificador del registro. |
| id\_actividad | INT FK | NOT NULL | Actividad evaluada. |
| id\_alumno | INT FK | NOT NULL | Alumno evaluado. |
| calificacion | DECIMAL(4,2) |   | Calificación de la actividad (0.00–10.00). |
| updated\_at | DATETIME |   | Última modificación. |

 

## **5.11 Tabla: materia**

| Campo | Tipo | Restricciones | Descripción |
| ----- | ----- | ----- | ----- |
| id\_materia | INT PK AI | NOT NULL | Identificador de la materia. |
| nombre | VARCHAR(120) | NOT NULL | Nombre completo de la materia. |
| siglas | VARCHAR(20) | NOT NULL | Siglas de la materia. |
| descripcion | TEXT |   | Descripción breve. |
| activo | TINYINT(1) | DEFAULT 1 | Estado de la materia. |

 

## **5.12 Relaciones Clave (ERD simplificado)**

•   	docente 1 — N grupo

•   	grupo N — M alumno (a través de grupo\_alumno)

•   	grupo 1 — N sesion

•   	sesion 1 — N asistencia (por alumno)

•   	grupo \+ alumno \+ parcial 1 — 1 examen\_escrito

•   	grupo \+ alumno \+ parcial 1 — 1 examen\_oral

•   	grupo 1 — N actividad (tipo portafolio o tarea)

•   	actividad 1 — N calificacion\_actividad (por alumno)

 

# **6\. Casos de Uso**

## **CU-01: Acceder al Sistema**

| Identificador | CU-01 |
| :---- | :---- |
| **Nombre** | Acceder al Sistema |
| **Actor(es)** | Docente |
| **Descripción** | El docente ingresa a ClassHub y el sistema lo autentica. |
| **Precondiciones** | El docente tiene acceso a la URL del sistema. |
| **Flujo principal** | 1\. El docente abre el navegador y accede a la URL. 2\. El sistema presenta la pantalla de login. 3\. El docente presiona el botón de acceso. 4\. El sistema carga el perfil del docente de ejemplo y redirige al Home. |
| **Flujos alternos** | 4a. Credenciales inválidas (versiones futuras): el sistema muestra un mensaje de error. |
| **Postcondiciones** | El docente accede a la vista Home con sus grupos disponibles. |

 

## **CU-02: Registrar Grupo**

| Identificador | CU-02 |
| :---- | :---- |
| **Nombre** | Registrar Grupo |
| **Actor(es)** | Docente |
| **Descripción** | El docente crea un nuevo grupo con sus datos correspondientes. |
| **Precondiciones** | El docente ha iniciado sesión. |
| **Flujo principal** | 1\. El docente accede a la sección de Administración de Grupos. 2\. Selecciona 'Nuevo Grupo'. 3\. Ingresa Carrera, Siglas, Cuatrimestre, Grupo y Periodo. 4\. El sistema genera automáticamente el Ciclo. 5\. El docente confirma y el sistema guarda el registro. |
| **Flujos alternos** | 3a. Datos incompletos: el sistema resalta los campos requeridos. |
| **Postcondiciones** | El grupo aparece en el listado del docente. |

 

## **CU-03: Tomar Asistencia**

| Identificador | CU-03 |
| :---- | :---- |
| **Nombre** | Tomar Asistencia |
| **Actor(es)** | Docente |
| **Descripción** | El docente registra la asistencia de los alumnos para una sesión. |
| **Precondiciones** | El docente tiene un grupo seleccionado con sesiones registradas y alumnos dados de alta. |
| **Flujo principal** | 1\. El docente selecciona la vista Attendance. 2\. Presiona 'Tomar Asistencia'. 3\. El sistema inicializa todos los alumnos en estado Asistencia (verde). 4\. El docente modifica individualmente los estados. 5\. El docente guarda. |
| **Flujos alternos** | 2a. No hay sesiones registradas: el sistema muestra mensaje informativo. |
| **Postcondiciones** | Los estados de asistencia quedan almacenados en la BD para la sesión. |

 

## **CU-04: Capturar Calificación de Examen Escrito**

| Identificador | CU-04 |
| :---- | :---- |
| **Nombre** | Capturar Calificación de Examen Escrito (WE) |
| **Actor(es)** | Docente |
| **Descripción** | El docente ingresa las calificaciones del examen escrito por parcial. |
| **Precondiciones** | El grupo y los alumnos están dados de alta. |
| **Flujo principal** | 1\. El docente selecciona la vista Write Exam. 2\. Ingresa la calificación de cada alumno para el parcial correspondiente (0.00–10.00). 3\. El sistema marca en rojo los valores inferiores a 7.00. 4\. El docente guarda. |
| **Flujos alternos** | 2a. Valor fuera de rango: el sistema muestra advertencia y descarta el valor. |
| **Postcondiciones** | Las calificaciones se almacenan en la tabla examen\_escrito. |

 

## **CU-05: Consultar Calificaciones (Alumno)**

| Identificador | CU-05 |
| :---- | :---- |
| **Nombre** | Consultar Calificaciones (Alumno) |
| **Actor(es)** | Alumno |
| **Descripción** | El alumno consulta sus calificaciones y asistencias. |
| **Precondiciones** | El alumno tiene matrícula registrada en al menos un grupo. |
| **Flujo principal** | 1\. El alumno accede al portal de consulta e ingresa su matrícula. 2\. El sistema presenta sus grupos activos. 3\. El alumno selecciona un grupo. 4\. El sistema muestra sus calificaciones y asistencias en modo lectura. |
| **Flujos alternos** | 1a. Matrícula no encontrada: el sistema muestra mensaje de error. |
| **Postcondiciones** | El alumno visualiza su información académica sin posibilidad de edición. |

 

# **7\. Reglas de Negocio**

| ID | Regla |
| :---: | ----- |
| RN-01 | La calificación mínima aprobatoria es 7.00. Cualquier calificación inferior se resalta visualmente en rojo. |
| RN-02 | La escala de calificación va de 0.00 a 10.00. No se aceptan valores fuera de este rango. |
| RN-03 | Todas las calificaciones se almacenan y muestran con exactamente dos decimales. |
| RN-04 | El campo ciclo se genera automáticamente: dos últimos dígitos del año \+ 'C' \+ número de periodo (1=ene-abr, 2=may-ago, 3=sep-dic). |
| RN-05 | No se puede tomar asistencia en una fecha no registrada previamente como sesión del grupo. |
| RN-06 | Cada clic en la celda de asistencia avanza cíclicamente: Asistencia (verde) → Retardo (amarillo) → Falta (rojo) → Justificado (naranja) → Asistencia. |
| RN-07 | La calificación de Ser se calcula como: (sesiones con Asistencia \+ 0.5×Retardo \+ 1×Justificado) / Total sesiones × 10\. |
| RN-08 | La ponderación de Hacer es: OE × 0.60 \+ Promedio\_PF × 0.40. Este resultado pondera 60 % del total. |
| RN-09 | La calificación parcial final \= WE × 0.30 \+ Hacer × 0.60 \+ AT × 0.10. |
| RN-10 | El nombre del alumno siempre se muestra como: Apellido Paterno \+ Apellido Materno \+ Nombre(s). |
| RN-11 | La matrícula de un alumno debe ser única dentro de un grupo. |
| RN-12 | No se permitirá eliminar un grupo, alumno, sesión o actividad si existen datos capturados relacionados. |

 

# **8\. Restricciones del Sistema**

## **8.1 Restricciones Técnicas**

•   	El sistema se desarrollará como aplicación web con stack: backend Node.js / PHP / Python (a definir en fase de arquitectura), base de datos MySQL 8+ / PostgreSQL 14+, frontend React / Vue (a definir).

•   	La versión 1.0 no incluye integración directa con el sistema SITO; la exportación se realizará mediante CSV o tabla imprimible.

•   	La funcionalidad offline no está contemplada en la versión 1.0.

 

## **8.2 Restricciones de Negocio**

•   	El modelo de evaluación está fijo para Inglés en v1.0. La parametrización por materia se contempla en versiones futuras.

•   	Solo se manejan tres parciales por grupo en cada ciclo.

•   	El periodo 'ene-abr' corresponde al primer cuatrimestre del año; 'may-ago' al segundo; 'sep-dic' al tercero.

 

# **9\. Apéndices**

## **9.1 Navegación y Vistas del Sistema**

| Código | Vista | Descripción |
| :---: | :---: | ----- |
| HM | Home | Dashboard estadístico \+ tabla de calificaciones parciales y promedio. |
| GP | Grupo | Administración de grupos: alta, edición y selección activa. |
| AT | Attendance | Registro de asistencia por sesión con estados de cuatro colores. |
| WE | Write Exam | Captura de calificación del examen escrito por parcial. |
| OE | Oral Exam | Captura de calificación del examen oral por parcial. |
| PF | Portfolio | Registro de actividades de portafolio con columnas dinámicas. |
| HW | Homework | Registro de tareas con columnas dinámicas. |
| EX | Exam | Vista resumen ponderada de calificaciones por parcial. |
| ST | SITO | Vista de calificaciones en formato del sistema institucional. |

 

## **9.2 Perfil de Docente de Ejemplo (v1.0)**

| Campo | Valor |
| :---: | ----- |
| Nombre completo | García López, María Elena |
| Materia | Inglés – Nivel Intermedio |
| Grupo de ejemplo | ING4A – Cuatrimestre 4 – Grupo A – 26C1 |
| Correo | mgarcia@uts.edu.mx |
| Acceso v1.0 | Botón único sin contraseña |

 

## **9.3 Historial de Revisiones**

| Versión | Fecha | Autor | Cambios |
| :---: | :---: | ----- | ----- |
| 1.0.0 | Abril 2026 | Equipo de Análisis – UTS | Documento inicial aprobado. |

 

Fin del Documento – ClassHub ERS v1.0  |  Universidad Tecnológica de Salamanca  |  Abril 2026  
