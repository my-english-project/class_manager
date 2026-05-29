# Adecuaciones

El sistema Class Manager que estamos diseñando para administrar las clases de inglés en academia de la UTS, es una plataforma que debe de permitir la administración de las clases por lo maestros, de tal manera que pueda ser una plataforma sencilla de manejar, esta debe permitir a los alumnos contar con una visión completa de su control de clase en cada skill evaluado.

Se deben identificar los tipos de usuarios del sistema:

1. Administrador del sistema.  
   1. Este usuario puede:  
      1. Crear ciclos y modificar su información.  
      2. Ver todos los grupos.  
      3. Ver los alumnos por grupo.  
      4. Ver las evaluaciones de los alumnos por skill.  
      5. Ver a los maestros dados de alta.  
      6. Buscar grupos, maestros, alumnos.  
      7. Dar de alta maestros y editar sus datos.  
      8. Ver los grupos por maestro.  
      9. Crear los grupos y asignarlos a los maestros así como modificar estas asignaciones.  
      10. Promover un grupo al siguiente nivel o grado.  
   2. Este usuario no puede:  
      1. Crear grupos.  
      2. Dar de alta alumnos.  
      3. Alterar los valores de los skills de los alumnos.  
2. Usuario del sistema (Maestro):  
   1. Este usuario puede:  
      1. Seleccionar ciclos.  
      2. Seleccionar grupos.  
      3. Ver solo sus grupos por ciclo.  
      4. Agregar alumnos a sus grupos.  
      5. Modificar los valores de los skills de sus alumnos.  
      6. Buscar solo a los alumnos de sus grupos.  
   2. Este usuario no puede:  
      1. Crear o modificar ciclos.  
      2. Crear o modificar grupos.  
      3. Ver información de grupos que no le pertenezcan.  
      4. Ver infiormación de otros maestros o alumnos que no sean los de sus grupos.  
      5. Promover grupos.  
3. Usuario del sistema (Alumno):  
   1. Este usuario solo puede consultar la información relacionada con él, no tiene permitido entrar a las secciones de Administrador o Maestro, por lo que su portal es diferente y solo de consulta, solo podrá ver sus calificaciones por skill y totales por parcial.

Como ejemplo crear al usuario

1. Administrador  
   1. Administrador \- Usuario/correo: [isaurouts@gmail.com](mailto:isaurouts@gmail.com), contraseña: ADMIN01  
2. Maestros  
   1. Isauro Rios \- [jrios@utsalamanca.edu.mx](mailto:jrios@utsalamanca.edu.mx), contraseña: PROF01  
   2. Alejandro Rios \- [irios@utsalamanca.edu.mx](mailto:irios@utsalamanca.edu.mx), contraseña: PROF02  
3. Alumnos  
   1. Aline Campos \- [612310471@utsalamanca.edu.mx](mailto:612310471@utsalamanca.edu.mx), contraseña: ALUM01  
   2. Emily Arredondo \- [612310503@utsalamanca.edu.mx](mailto:612310503@utsalamanca.edu.mx), contraseña: ALUM02

Para este ejemplo, permite el login solo con el nombre del correo omitiendo el dominio (isaurouts, jrios, irios, [612310471](mailto:612310471@utsalamanca.edu.mx), [612310503](mailto:612310503@utsalamanca.edu.mx)) y sus respectivas contraseñas. Relaciona a los alumnos de ejemplo con los que ya se encuentran en la BD a través de la matricula.

Ten en cuenta que los usuarios del sistema (alumnos) se darán de alta ellos mismos, esto proporcionando su matricula y el sistema verificará si existen en la base de datos, de ser así, le mostrará su nombre y le pedira que cree una contraseña. A futuro esta alta deberá estar respaldada con un correo enviado para verificar, este paso aún no se hará.

