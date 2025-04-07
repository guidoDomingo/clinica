-- Agregar columna profile_photo a la tabla rh_person
ALTER TABLE public.rh_person
ADD COLUMN profile_photo VARCHAR(255) NULL;

-- Comentario para la columna
COMMENT ON COLUMN public.rh_person.profile_photo IS 'Ruta de la imagen de perfil del usuario';