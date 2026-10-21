const searchForm = document.querySelector('#search-form');
const searchButton = document.querySelector('#search-button');
const searchSpinner = document.querySelector('#search-spinner');
const searchLabelText = document.querySelector('#search-label-text');
const cityInput = document.querySelector('#city');

const locateButton = document.querySelector('#locate-button');
const locateLabelText = document.querySelector('#locate-label-text');
const locationForm = document.querySelector('#location-form');
const locationStatus = document.querySelector('#location-status');
const latitudeInput = document.querySelector('#latitude');
const longitudeInput = document.querySelector('#longitude');

const loadingSkeleton = document.querySelector('#weather-loading-skeleton');
const weatherContent = document.querySelector('#weather-content');

function showLoadingState(searchingText) {
    // Show skeleton UI reserving 1:1 space
    if (loadingSkeleton) {
        loadingSkeleton.classList.remove('hidden');
    }
    if (weatherContent) {
        weatherContent.classList.add('hidden');
    }

    // Update search button
    if (searchButton) {
        searchButton.disabled = true;
        searchButton.setAttribute('aria-busy', 'true');
        if (searchSpinner) searchSpinner.classList.remove('hidden');
        if (searchLabelText && searchingText) searchLabelText.textContent = searchingText;
    }

    // Disable location button during fetch to prevent duplicate requests
    if (locateButton) locateButton.disabled = true;
}

if (searchForm) {
    searchForm.addEventListener('submit', () => {
        const searchingText = searchButton?.dataset.searching || 'Searching…';
        showLoadingState(searchingText);
    });
}

function handleGeolocation() {
    if (!('geolocation' in navigator)) {
        const unavailableMsg = locateButton?.dataset.unavailable || 'Location unavailable';
        if (locationStatus) {
            locationStatus.textContent = unavailableMsg;
            locationStatus.classList.remove('hidden');
            locationStatus.classList.add('flex');
        }
        return;
    }

    const locatingText = locateButton?.dataset.locating || 'Locating…';
    if (locateButton) {
        locateButton.disabled = true;
        locateButton.setAttribute('aria-busy', 'true');
        if (locateLabelText) locateLabelText.textContent = locatingText;
    }

    if (locationStatus) {
        locationStatus.textContent = locatingText;
        locationStatus.classList.remove('hidden');
        locationStatus.classList.add('flex');
    }

    showLoadingState(locatingText);

    navigator.geolocation.getCurrentPosition(
        ({ coords }) => {
            if (latitudeInput && longitudeInput && locationForm) {
                latitudeInput.value = coords.latitude;
                longitudeInput.value = coords.longitude;
                locationForm.requestSubmit();
            }
        },
        () => {
            const deniedMsg = locateButton?.dataset.denied || 'Location access was denied';
            if (locationStatus) {
                locationStatus.textContent = deniedMsg;
                locationStatus.classList.remove('hidden');
                locationStatus.classList.add('flex');
            }

            // Restore buttons
            if (locateButton) {
                locateButton.disabled = false;
                locateButton.removeAttribute('aria-busy');
                if (locateLabelText) locateLabelText.textContent = locateButton.dataset.defaultLabel ?? 'Use my location';
            }
            if (searchButton) {
                searchButton.disabled = false;
                searchButton.removeAttribute('aria-busy');
                if (searchSpinner) searchSpinner.classList.add('hidden');
                if (searchLabelText) searchLabelText.textContent = searchButton.dataset.defaultLabel ?? 'Search';
            }

            // Restore content if error occurred
            if (loadingSkeleton) loadingSkeleton.classList.add('hidden');
            if (weatherContent) weatherContent.classList.remove('hidden');
        },
        {
            enableHighAccuracy: false,
            maximumAge: 0,
            timeout: 10000,
        },
    );
}

if (locateButton) {
    locateButton.addEventListener('click', handleGeolocation);
}

const futureForm = document.querySelector('#future-forecast-form');
const futureButton = document.querySelector('#future-button');
const futureSpinner = document.querySelector('#future-spinner');
const futureLabelText = document.querySelector('#future-label-text');

if (futureForm) {
    futureForm.addEventListener('submit', () => {
        if (futureButton) {
            futureButton.disabled = true;
            futureButton.setAttribute('aria-busy', 'true');
            if (futureSpinner) futureSpinner.classList.remove('hidden');
            if (futureLabelText) futureLabelText.textContent = futureButton.dataset.generating || 'Analyzing…';
        }
    });
}

