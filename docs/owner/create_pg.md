1) PURPOSE
create pg feature of owner module allows owner to enter PG name, PG Address and Location details. The application then validates the owner input and information, retrieves geographic coordinates of pg through Delhivery map geocoding api, and then saves the information in database.

the saved pg details are then used as base in subsequent steps of owner module workflow.

2) REQUIREMENT
* Only authenticated owners can create a PG.
* The owner must provide the required PG information.
* Owner id must come from session.
* The owner selects a state, city, and area using cascading dropdowns.
* The selected area determines the location_id associated with the PG.
* The application validates the submitted information and obatain latitude and longitude through geocoding.
* The PG record is associated with the currently authenticated owner.
* After successful creation, the owner is redirected to the next step in the PG creation workflow.
* Refreshing the page after successful submission must not create another PG.

3) DATABASE DESIGN
-- Main Table -> pgs
* pg-id : unqiue identifier for PG.
* owner-id : identifies the owner who created pg.
* location-id: reference the selected area in location hierarchy.
* pg_name : full name og pg.
* address: full address of pg.
* latitude: geographical latitude of pg retreived from geocoding pg address.
* longitude : geographical latitude of pg retreived from geocoding pg address.
 
** pgs table carries both: location_id for locality/filtering and its own geocoded coordinates for exact distance calculations.

-- Relationships

* One owner can create multiple PG listings.
* Each PG belongs to one owner.
* Each PG is associated with a selected location.
* Each PG receives a unique pg_id, which is used in subsequent management steps.

-- Design Decision: Location and Address

The application uses predefined state, city, and area records to identify the selected location through location_id.

The full property address is stored separately. Geocoding converts the address into geographical coordinates, which can support future location-based searches and distance calculations.

4) APPLICATION FLOW

* The owner opens the Create PG page.
* The controller checks the user's authentication and handles the HTTP request.
* The owner enters the PG name and address and selects a state, city, and area.
* JavaScript requests the relevant cities and areas through AJAX as the selections change.
* The form submits the selected location_id and other PG information.
* PGService validates the submitted information and coordinates the creation process.
* GeocodeService communicates with the Delhivery Maps Geocoding API to obtain latitude and longitude.
* PG::create() inserts the PG information into MySQL and returns the new pg_id.
* The controller stores a flash message and redirects the owner to the next step.
* The request ends after the redirect, preventing a browser refresh from resubmitting the original POST request.

Owner enters:
    PG name
    State / City / Area
    Exact PG address
             ↓
Application finds location_id
             ↓
Application geocodes exact address
             ↓
Application gets latitude + longitude
             ↓
PG.php create()
             ↓
INSERT all required basic PG data

///////////////////////

Owner creates PG
       ↓
draft + pending
       ↓
Owner completes rooms/charges/images
       ↓
submit listing
       ↓
verification
       ↓
verified
       ↓
active

5) FILES & RESPONSIBILITIES
// 
The PHP page (controller) handles the HTTP request.
The service handles business logic.
The model handles database operations.

** controller : create-pg.php => 
require owner authentication
        ↓
get SessionManager::userId()
        ↓
display success flash message
        ↓
handle POST request
        ↓
validate
        ↓
call PGService
        ↓
redirect/display result

** Service : PGService.php => 
validate submitted PG information
coordinate location validation, geocoding and PG creation.
pass validated data to model

** Model : PG.php =>
Execute database operations related to PG records.
Insert the new PG record.
Return the newly created PG identifier.

** Geocoding Service : GeocodeService.php => 
Communicate with the Delhivery Maps Geocoding API.
Process the geocoding response.
Provide the geographical coordinates to the PG creation workflow.

** Location Lookup : create-pg.js , locations.php (Ajax endpoint for js communication)
The location lookup endpoint and related JavaScript support the cascading State → City → Area dropdowns.

6) VALIDATION, SECURITY

* The creation workflow includes validation of the owner, PG information, and selected location.

* The authenticated owner's ID is obtained from the session rather than trusted as a user-submitted owner ID.

* Required-field validation.
* Validation that the selected location exists.
* CSRF token verification, if enforced by the controller.
* PDO prepared statements for database operations.
* Safe handling of geocoding failures and invalid API responses.
* Protection of API credentials from public source control.

7) TESTING

Valid PG information
missing required field
invalid location selection
geocoding succeeds
geocoding fails
successfull submission followed by refresh
unauthenticated access

Owner can create PG        ✓
Normal user cannot access page        ✓
owner_id comes from session        ✓
invalid input rejected        ✓
PG inserted with draft status         ✓
verification starts as pending        ✓
latitude/longitude are valid          ✓
foreign key location_id works         ✓

8) DECISIONS 

--Separation of Responsibilities
The controller handles HTTP requests and redirects, the service handles business rules and validation, and the model handles SQL operations.

This keeps database queries out of the service layer and makes each component easier to maintain.

--Geocoding
Geocoding is handled by a dedicated service rather than being mixed into the controller or model. This separates external API communication from request handling and database operations.

--Post/Redirect/Get (PRG)
After successful creation, the application redirects to a GET request rather than leaving the browser on the POST response.

This addresses the duplicate-record problem previously encountered when refreshing the page after submission.

9) PROBLEMS
// problem 1
- **Problem:** after pg details submission, refreshing the page create another pg with same details.
- **Cause:** Refreshing the browser could resubmit the previous POST request.?
- **Solution:** Implemented the Post/Redirect/Get pattern after successful creation, followed by a redirect and termination of the current request.
- **Lesson learned:** A successful form submission should redirect to a GET page to prevent accidental resubmission.

//problem 2
- **Problem:** after pg details submission, success message was not displayed.
- **Cause:** getFlash() is being called on the redirected GET request.
- **Solution:** getFlash() should be called before the POST handling.
- **Lesson learned:** A successful form submission should redirect to a GET page to prevent accidental resubmission.