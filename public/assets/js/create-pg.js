document.addEventListener('DOMContentLoaded', () => {

  const stateSelect = document.getElementById('state');
  const citySelect = document.getElementById('city');
  const areaSelect = document.getElementById('area');

  document.getElementById('create-pg-form')?.addEventListener('submit', function () {
    const submitButton = document.getElementById('create-pg-submit');

    if (submitButton) {
      submitButton.disabled = true;
      submitButton.textContent = 'Creating PG…';
    }
  });

  loadStates();

  async function loadStates() {
    try {
      const response = await fetch(
        '/pg_recommendation/public/owner/locations.php?action=states'
      );
      const result = await response.json();

      if (!result.success) {
        throw new Error(result.message);
      }

      result.data.forEach(state => {
        const option = document.createElement('option');
        option.value = state;
        option.textContent = state;

        stateSelect.appendChild(option);
      });
    } catch (error) {
      console.error('Unable to load states')
    }
  }

  stateSelect.addEventListener('change', async () => {

    const state = stateSelect.value;

    resetCitiesAndAreas();

    if (!state) {
      return;
    }

    await loadCities(state);
  });

  async function loadCities(state) {

    try {

      citySelect.disabled = true;

      const response = await fetch(
        `/pg_recommendation/public/owner/locations.php?action=cities&state=${encodeURIComponent(state)}`
      );

      const result = await response.json();

      if (!result.success) {
        throw new Error(result.message);
      }

      result.data.forEach(city => {

        const option = document.createElement('option');

        option.value = city;
        option.textContent = city;

        citySelect.appendChild(option);
      });

      citySelect.disabled = false;

    } catch (error) {

      console.error('Unable to load cities:', error);
    }
  }

  citySelect.addEventListener('change', async () => {
    const state = stateSelect.value;
    const city = citySelect.value;

    resetAreas();

    if (!state || !city) {
      return;
    }

    await loadAreas(state, city);
  });

  async function loadAreas(state, city) {
    try {
      areaSelect.disabled = true;

      const response = await fetch(
        `/pg_recommendation/public/owner/locations.php?action=areas&state=${encodeURIComponent(state)}&city=${encodeURIComponent(city)}`
      );

      const result = await response.json();

      if (!result.success) {
        throw new Error(result.message);
      }

      result.data.forEach(location => {
        const option = document.createElement('option');
        option.value = location.location_id;
        option.textContent = location.area;

        areaSelect.appendChild(option);
      });

      areaSelect.disabled = false;

    } catch (error) {
      console.error('Unable to load areas.');
    }
  }

  function resetAreas() {
    areaSelect.innerHTML = '<option value="">Select Area</option>';

    areaSelect.disabled = true;
  }

  function resetCitiesAndAreas() {
    citySelect.innerHTML = '<option value="">Select City</option>';

    areaSelect.innerHTML = '<option value="">Select Area</option>';

    citySelect.disabled = true;
    areaSelect.disabled = true;
  }

})