-- Script para insertar datos de prueba en las tablas relacionadas con consultas médicas

-- Primero insertamos algunos pacientes de prueba en la tabla rh_person
INSERT INTO rh_person (document_number, birth_date, first_name, last_name, phone_number, gender, record_number, address, email, department_id, city_id, is_minor, guardian_name, guardian_document, is_active)
VALUES
('1234567', '1980-05-15', 'Juan', 'Pérez', '0981123456', 'M', 'P001', 'Av. España 123', 'juan.perez@email.com', 1, 1, false, NULL, NULL, true),
('2345678', '1975-08-22', 'María', 'González', '0982234567', 'F', 'P002', 'Calle Palma 456', 'maria.gonzalez@email.com', 1, 1, false, NULL, NULL, true),
('3456789', '1990-03-10', 'Carlos', 'Rodríguez', '0983345678', 'M', 'P003', 'Av. Sacramento 789', 'carlos.rodriguez@email.com', 2, 2, false, NULL, NULL, true),
('4567890', '2015-11-05', 'Ana', 'Martínez', '0984456789', 'F', 'P004', 'Calle Mcal. López 234', 'ana.martinez@email.com', 1, 1, true, 'Pedro Martínez', '1122334', true),
('5678901', '1965-07-30', 'Roberto', 'Sánchez', '0985567890', 'M', 'P005', 'Av. Mariscal Estigarribia 567', 'roberto.sanchez@email.com', 3, 3, false, NULL, NULL, true);

-- Insertamos datos de prueba en la tabla de consultas
INSERT INTO consultas (id_persona, motivoscomunes, txtmotivo, visionod, visionoi, tensionod, tensionoi, consulta_textarea, receta_textarea, txtnota, proximaconsulta, whatsapptxt, email, id_user)
VALUES
-- Consulta para Juan Pérez
(1, 'Control rutinario', 'Revisión anual programada', '20/20', '20/20', '14 mmHg', '15 mmHg', 
'Paciente acude a consulta para control anual. No refiere molestias significativas. Examen de fondo de ojo normal. Presión intraocular dentro de rangos normales.', 
'No requiere prescripción de lentes correctivos en este momento.', 
'Próximo control en 12 meses.', 
'2023-12-15', '0981123456', 'juan.perez@email.com', 1),

-- Consulta para María González
(2, 'Visión borrosa', 'Dificultad para ver de lejos', '20/40', '20/30', '16 mmHg', '16 mmHg', 
'Paciente refiere dificultad progresiva para ver de lejos en los últimos 3 meses. Examen muestra miopía leve en ambos ojos.', 
'Rx:\nOD: -1.00 -0.50 x 180\nOI: -0.75 -0.25 x 175\n\nLentes monofocales con antireflejo. Uso permanente.', 
'Recomendar descansos visuales frecuentes durante el uso de pantallas.', 
'2023-09-20', '0982234567', 'maria.gonzalez@email.com', 1),

-- Consulta para Carlos Rodríguez
(3, 'Irritación', 'Ojos rojos y sensación de cuerpo extraño', '20/25', '20/25', '15 mmHg', '14 mmHg', 
'Paciente presenta irritación ocular bilateral de 5 días de evolución. Refiere sensación de cuerpo extraño y sequedad. Trabaja muchas horas frente a la computadora.', 
'Gotas lubricantes sin conservantes.\nAplicar 1 gota en cada ojo 4 veces al día.\nContinuar por 15 días.', 
'Evaluar síndrome de ojo seco en próxima consulta si los síntomas persisten.', 
'2023-08-05', '0983345678', 'carlos.rodriguez@email.com', 2),

-- Consulta para Ana Martínez (menor de edad)
(4, 'Control rutinario', 'Primera revisión oftalmológica', '20/30', '20/30', '12 mmHg', '12 mmHg', 
'Primera revisión oftalmológica. Paciente colaboradora. Madre refiere que la niña se acerca mucho a los libros y a la televisión.', 
'Se detecta hipermetropía leve. Se recomienda seguimiento en 6 meses sin prescripción por ahora.', 
'Vigilar si presenta síntomas como dolor de cabeza o mayor dificultad visual.', 
'2023-11-10', '0984456789', 'ana.martinez@email.com', 1),

-- Consulta para Roberto Sánchez
(5, 'Actualización de lentes', 'Lentes actuales insuficientes', '20/50', '20/60', '18 mmHg', '17 mmHg', 
'Paciente acude para actualización de lentes. Refiere que con sus lentes actuales (de hace 3 años) ya no ve bien. Se detecta progresión de presbicia.', 
'Rx:\nOD: +2.00 -0.25 x 90 Add +2.50\nOI: +2.25 -0.50 x 85 Add +2.50\n\nLentes progresivos con antireflejo y protección luz azul.', 
'Explicado al paciente el período de adaptación a los lentes progresivos.', 
'2024-01-15', '0985567890', 'roberto.sanchez@email.com', 2),

-- Segunda consulta para Juan Pérez (seguimiento)
(1, 'Dolor ocular', 'Dolor en ojo derecho desde hace 3 días', '20/25', '20/20', '15 mmHg', '14 mmHg', 
'Paciente acude por dolor en ojo derecho de 3 días de evolución. Refiere sensación punzante ocasional. No trauma previo. Se observa leve hiperemia conjuntival.', 
'Colirio antibiótico-antiinflamatorio:\nAplicar 1 gota en ojo derecho cada 6 horas por 7 días.', 
'Volver a consulta si los síntomas no mejoran en 48 horas.', 
'2023-08-10', '0981123456', 'juan.perez@email.com', 2);

-- Insertamos datos de prueba en la tabla de archivos
INSERT INTO archivos (nombre_archivo, ruta_archivo, id_usuario, id_persona, origen, observaciones, tamano_archivo, tipo_archivo, checksum)
VALUES
-- Archivos para Juan Pérez
('retinografia_juan_perez.jpg', '/uploads/imagenes/2023/07/retinografia_juan_perez.jpg', 1, 1, 'consulta', 'Retinografía de control anual', 2458600, 'image/jpeg', 'a1b2c3d4e5f6g7h8i9j0'),
('presion_ocular_juan.pdf', '/uploads/documentos/2023/07/presion_ocular_juan.pdf', 1, 1, 'consulta', 'Registro de presión ocular', 1245800, 'application/pdf', 'k1l2m3n4o5p6q7r8s9t0'),

-- Archivos para María González
('receta_maria_gonzalez.pdf', '/uploads/documentos/2023/06/receta_maria_gonzalez.pdf', 1, 2, 'consulta', 'Receta de lentes', 985600, 'application/pdf', 'u1v2w3x4y5z6a7b8c9d0'),
('topografia_corneal_maria.jpg', '/uploads/imagenes/2023/06/topografia_corneal_maria.jpg', 1, 2, 'consulta', 'Topografía corneal', 3568900, 'image/jpeg', 'e1f2g3h4i5j6k7l8m9n0'),

-- Archivos para Carlos Rodríguez
('biomicroscopia_carlos.jpg', '/uploads/imagenes/2023/05/biomicroscopia_carlos.jpg', 2, 3, 'consulta', 'Biomicroscopía para evaluación de irritación', 1856700, 'image/jpeg', 'o1p2q3r4s5t6u7v8w9x0'),

-- Archivos para Ana Martínez
('examen_visual_ana.pdf', '/uploads/documentos/2023/05/examen_visual_ana.pdf', 1, 4, 'consulta', 'Resultados de examen visual pediátrico', 1356800, 'application/pdf', 'y1z2a3b4c5d6e7f8g9h0'),
('autorizacion_padres.pdf', '/uploads/documentos/2023/05/autorizacion_padres.pdf', 1, 4, 'administrativo', 'Autorización de los padres para tratamiento', 856400, 'application/pdf', 'i1j2k3l4m5n6o7p8q9r0'),

-- Archivos para Roberto Sánchez
('medicion_presbicia_roberto.jpg', '/uploads/imagenes/2023/04/medicion_presbicia_roberto.jpg', 2, 5, 'consulta', 'Medición de presbicia', 2156700, 'image/jpeg', 's1t2u3v4w5x6y7z8a9b0'),
('historial_prescripciones.pdf', '/uploads/documentos/2023/04/historial_prescripciones.pdf', 2, 5, 'consulta', 'Historial de prescripciones anteriores', 1756900, 'application/pdf', 'c1d2e3f4g5h6i7j8k9l0');

-- Relacionamos archivos con consultas en la tabla archivos_consulta
INSERT INTO archivos_consulta (id_consulta, id_archivo)
VALUES
-- Archivos para la primera consulta de Juan Pérez (id_consulta = 1)
(1, 1),  -- retinografía
(1, 2),  -- presión ocular

-- Archivos para la consulta de María González (id_consulta = 2)
(2, 3),  -- receta
(2, 4),  -- topografía corneal

-- Archivos para la consulta de Carlos Rodríguez (id_consulta = 3)
(3, 5),  -- biomicroscopía

-- Archivos para la consulta de Ana Martínez (id_consulta = 4)
(4, 6),  -- examen visual
(4, 7),  -- autorización padres

-- Archivos para la consulta de Roberto Sánchez (id_consulta = 5)
(5, 8),  -- medición presbicia
(5, 9),  -- historial prescripciones

-- Archivos para la segunda consulta de Juan Pérez (id_consulta = 6)
(6, 1);  -- reutilizamos la retinografía para comparación