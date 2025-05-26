# Fix for doctor_estado issue

This change fixes an issue with how the doctor's status was being handled in the medical clinic reservation system.

## Issues Fixed:

1. The `doctor_estado` field is a VARCHAR column with value 'ACTIVO', but was being compared to boolean `true` in SQL queries.
2. The display code was evaluating `doctor_estado` as a boolean rather than comparing it to the string value 'ACTIVO'.

## Files Modified:

- `model/servicios.model.php`: Updated query to compare `doctor_estado` with 'ACTIVO' instead of boolean true
- `diagnostico_doctores_fecha.php`: Updated query and display code to handle string comparison
- `test_sql_medicos.php`: Updated query and display logic to handle string comparison

## Testing:

To verify the changes, you can use:

1. `test_doctor_estado.php` - A simple script to test the SQL query with the corrected condition
2. `diagnostico_doctores_fecha.php` - The diagnostic tool now correctly handles doctor_estado as a string

## Validation:

The system can now correctly find doctors by date based on their status being 'ACTIVO'.
