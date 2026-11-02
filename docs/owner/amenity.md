# Amenities Module — Owner Module

## 1. Purpose

The Amenities module allows an authenticated PG owner to select the amenities available at their PG and specify whether each selected amenity is included in the rent or charged separately.

The module retrieves predefined amenities from the `amenities` table and saves the owner's selections in the `pg_amenities` junction table.


## 2. Database Design

### A. `amenities` — Master Table

The `amenities` table stores the predefined list of amenities available for selection.

 `amenity_id` -  Identifies an amenity 
 `name` - Name of the amenity 

The `findAll()` model method retrieves the amenity IDs and names, ordered alphabetically.

### B. `pg_amenities` — Junction Table

The `pg_amenities` table connects PG listings with amenities.

 `pg_id` - Identifies the PG 
 `amenity_id` - Identifies the selected amenity 
 `included_in_rent` - Indicates whether the amenity is included in rent. 
 Each selected amenity has an `included_in_rent` value:
- `1` — The amenity is included in the rent.
- `0` — The amenity is charged separately.

* An amenity marked `0` is not an unselected amenity. It is selected for the PG but is not included in the rent.

## 3. Application Flow
- Controller validates the pg_id and verifies PG ownership.
- AmenityService retrieves available amenities and the PG's saved selections.
- Owner selects amenities and sets their rent-inclusion status.
- Controller verifies the CSRF token.
- Service validates the submitted data and checks that amenity IDs exist.
- Model replaces the PG's existing amenity associations inside a database transaction.
- On success, the owner is redirected to the Charges page.

/// How Updating Amenities Works

The model's `replaceForPg()` method follows a replace-all approach:

1. Begin a database transaction.
2. Delete the existing `pg_amenities` associations for the selected PG.
3. Insert the currently submitted selections.
4. Commit the transaction if the operations succeed.
5. Roll back the transaction if an exception occurs.

## 4. Files and Responsibilities

- - Controller :  `public/owner/amenities.php`  Handles access checks, GET/POST requests, CSRF verification, feedback, and redirects. 

- - View : `views/owner/amenity-form.php`  Displays the amenity selection form and saved selections. 

- - Service : `src/services/AmenityService.php`  Coordinates ownership verification, validation, ID checks, and model operations.

- - Model : `src/models/Amenity.php`  Executes SQL for retrieving amenities and replacing PG-amenity associations. 

## 5. Security & Validation

- - Validator: `src/validators/AmenityValidator.php`  Validates amenity IDs and `included_in_rent` values

- - `src/services/AuthorizationService.php`  Verifies that the owner can manage the selected PG 
- - `src/security/Csrf.php`  Provides CSRF token verification 
- - `src/services/SessionManager.php`  Provides session and flash-message handling 
- - `src/middleware/AuthMiddleware.php`  Restricts access to authenticated owners 
- - `config/db.php`  Provides the database connection 

## 6. Relationship with the Charges Module

The Amenities module determines which amenities are included in the rent and which require separate charges.

The model method `findSeparateChargeAmenitiesByPgId()` joins `pg_amenities` with `amenities` and filters records where `included_in_rent = 0`.

It returns the junction-table record ID as `pg_amenity_id`, along with the amenity name and related information.

The Charges module uses this `pg_amenity_id` to associate a separate charge with the specific amenity selection for that PG. This is different from the master `amenity_id`.

**Important distinction:**

- `amenity_id` identifies the general amenity, such as Wi-Fi.
- `pg_amenity_id` identifies that amenity's association with a particular PG.

This allows a charge to reference the correct PG-specific amenity association.

## 7. Testing
 Test cases

- Load amenities and saved selections
- Save and update selections
- Handle an empty selection
- Reject invalid amenity IDs or rent-inclusion values
- Reject invalid CSRF tokens
- Prevent unauthorized PG updates
- Roll back a failed database update
- Redirect successfully to Charges 

## 8. Problems and Solutions

**Issue 1**: Selected amenities were not loading correctly.

- Problem: The checkboxes were not correctly reflecting which amenities had already been saved for a particular PG.

- Cause: The findByPgId() model method returned a numerically indexed array of database rows, but the view expected $selectedAmenities to be indexed directly by amenity_id.
  
  model returned : [
    0 => [
        'amenity_id' => 1,
        'included_in_rent' => 1
    ],
    1 => [
        'amenity_id' => 3,
        'included_in_rent' => 0
    ]
  ]

  view expected : [
    1 => 1,
    3 => 0
  ]

- solution : Transform the database rows into an array indexed by amenity_id in the controller.
     $selectedRows = $amenityService->getPgAmenities($pgId);
     
     $selectedAmenities = [];
     
     foreach ( $selectedRows as $row) {
         $selectedAmenities[(int) $row['amenity_id']]
             = (int) $row['included_in_rent'];
     }

- Learning : The structure of returned data matters. When passing data from the model through the controller to the view, ensure that the data structure matches how the view accesses it. isset($selectedAmenities[$amenityId]) works as intended only when the array is structured accordingly.

**Issue 2**: Amenity IDs were stored under the wrong array key

- Problem: The controller did not build the selected amenities array correctly before passing it to the service.

- Cause: The code used a quoted variable name:
$amenities['$amenityId'] = [
    'included_in_rent' => $data['included_in_rent'] ?? '1'
];
PHP treated '$amenityId' as a literal string instead of using the value stored in the variable.

- Solution: Remove the quotes around the variable:
$amenities[$amenityId] = [
    'included_in_rent' => $data['included_in_rent'] ?? '1'
];
This makes the actual amenity ID the array key.

- Learning: In PHP, quoted variable names are not evaluated as array keys. Use $array[$variable] when the key should come from a variable, and $array['key'] when the key is a literal string.