# Charges Module

## 1. Purpose

Allows PG owners to manage additional charges associated with their PG, including charges for amenities not included in rent and independent charge types.

The module keeps room rent, security deposits, and additional charges distinct so users can understand the actual cost components of a PG.

## 2. Database

### Main table: `charges`

- `charge_id` - Unique charge identifier 
- `pg_id` - PG associated with the charge 
- `pg_amenity_id` - Optional link to a separately charged PG amenity 
- `charge_name` - Name of the charge 
- `amount` - Charge amount 
- `charge_basis` - `fixed_monthly` or `per_unit` 
- `unit` - Unit used for a per-unit charge, where applicable 
- `description` - Additional information 

### Important relationships

- `pg_id` associates a charge with a PG.
- `pg_amenity_id` links an amenity-based charge to its specific record in `pg_amenities`.
- A charge with no `pg_amenity_id` can represent an independent charge type.

## 3. Application Flow

1. The owner arrives from the Amenities step with a `pg_id`.
2. The application verifies PG ownership and loads the existing charges.
3. The owner adds a charge or edits/deletes an existing one.
4. The service validates the submitted values and verifies that the charge belongs to the owner's PG.
5. The model performs the database operation.
6. The application provides feedback and redirects to the appropriate Charges page.

### Amenity-based charges

- The Amenities module identifies amenities where `included_in_rent = 0`.
- The Charges module uses the associated `pg_amenity_id`, not the master `amenity_id`.
- For an amenity-based charge, the charge name should remain tied to the selected amenity rather than being freely changed in the edit form.

## 4. Files and Responsibilities

- `public/owner/charges.php` - Loads charges and handles the main Charges workflow 
- `public/owner/edit-charge-form.php` - Displays the edit form 
- `public/owner/edit-charge.php` - Processes charge updates 
- `public/owner/delete-charge.php` - Processes charge deletion 
- `src/services/ChargeService.php` - Validation workflow and ownership checks 
 * Service now handles:
 
 * - PG ownership
 * - pg_amenity validation
 * - amenity → charge name
 * - independent charge name validation
 * - form input validation
 * - database creation through model

- `src/models/Charge.php` - Charge-related SQL queries 
- `src/models/Amenity.php` - Retrieves amenities that are charged separately 
- `src/services/AuthorizationService.php` - Verifies ownership of the PG or charge 

Deletion flow: delete-charge.php verifies ownership before calling the service, and the service verifies ownership again.

## 5. Key Decisions

- **Separate charges from room rent:** Additional charges must not be silently included in monthly room rent.
- **Keep deposits separate:** Security deposits belong to individual rooms and are not monthly charges.
- **Support two charge bases:** `fixed_monthly` represents a fixed monthly amount; `per_unit` represents a charge calculated using a unit.
- **Use `pg_amenity_id` for amenity-based charges:** This identifies the amenity association for the specific PG.
- **Do not change the amenity association during a normal charge update:** `Charge::update()` intentionally does not update `pg_amenity_id`.
- **Verify ownership through the associated PG:** A submitted charge ID alone is not sufficient authorization.

## 6. Testing and Issues

- Create an independent charge.
- Create a charge for a separately charged amenity.
- Load existing charges.
- Edit a charge.
- Delete a charge.
- Prevent changes to another owner's charge.
- Validate amount and charge basis.
- Handle optional `unit` and `description`.
- Keep amenity-based charge names tied to their amenities.
- Handle invalid or missing charge IDs.

## 7. Problems

* Issue 1 : Owner can access another owner's charges.php page

- **Problem:** Owner is able to open another owner's pg charge page and the application displays an error message but the page continues rendering, and the owner can still see the Add Charge form.

// The application displays the authorization error but does not fully block access to the protected page.

- **Cause:** The authorization check detects the unauthorized access but failed to terminate execution after authorization fails. Catching an exception does not automatically stop PHP from executing the remaining statements.

- **Solution:** The controller must stop processing the request when authorization fails. (set a flash message, redirect to the owner's dashboard, and call exit).

- **Learning:** An error message is not the same as access control.

Catching an exception does not terminate execution.

Controllers must stop the request or redirect after an access-denied result. 

* Issue 2 : Charge name remains disabled after selecting an amenity.

- **Problem:** After adding an amenity-based charge, the input remains disabled. Even after refreshing the page, it appears disabled for both amenity-based and independent charges.

//(When an amenity is selected, the charge name should be disabled because the name comes from the selected amenity.)

- **Cause:** the browser remembers the selected <select> value after the form submission/refresh, but JavaScript only changes the disabled state when change happens.

- **Solution** But the browser state should be determined every time the form loads, not just after a change event.

Not putting the logic only inside the event listener.

Created a function that determines the state:
function toggleChargeName() {
    if (pgAmenitySelect.value !== '') {
        chargeNameInput.disabled = true;
        chargeNameInput.value = '';
    } else {
        chargeNameInput.disabled = false;
    }
}

Then called it in two places:
pgAmenitySelect.addEventListener(
    'change',
    toggleChargeName
);

toggleChargeName();

- **Learning:** JavaScript should keep the UI synchronized with the current form state.

Initial page load and subsequent changes both need to be handled.