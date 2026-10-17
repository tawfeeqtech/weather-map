const searchForm = document.querySelector('#search-form');
const searchButton = document.querySelector('#search-button');
const searchLabel = document.querySelector('#search-label');
const locateButton = document.querySelector('#locate-button');
const locationForm = document.querySelector('#location-form');
const locationStatus = document.querySelector('#location-status');
const locateLabel = document.querySelector('#locate-label');

if (searchForm && searchButton && searchLabel) {
    searchForm.addEventListener('submit', () => {
        searchButton.disabled = true;
        searchButton.setAttribute('aria-busy', 'true');
        searchLabel.textContent = searchButton.dataset.searching ?? '';
    });
}

if (locateButton && locationForm && locationStatus && locateLabel) {
    locateButton.addEventListener('click', () => {
        if (!('geolocation' in navigator)) {
            locationStatus.textContent = locateButton.dataset.unavailable ?? '';
            return;
        }

        locateButton.disabled = true;
        locateButton.setAttribute('aria-busy', 'true');
        locateLabel.textContent = locateButton.dataset.locating ?? '';
        locationStatus.textContent = locateButton.dataset.locating ?? '';

        navigator.geolocation.getCurrentPosition(
            ({ coords }) => {
                document.querySelector('#latitude').value = coords.latitude;
                document.querySelector('#longitude').value = coords.longitude;
                locationForm.requestSubmit();
            },
            () => {
                locationStatus.textContent = locateButton.dataset.denied ?? '';
                locateButton.disabled = false;
                locateButton.removeAttribute('aria-busy');
                locateLabel.textContent = locateButton.dataset.defaultLabel ?? '';
            },
            {
                enableHighAccuracy: false,
                maximumAge: 0,
                timeout: 10000,
            },
        );
    });
}
