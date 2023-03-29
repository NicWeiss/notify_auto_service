import requests

from app.core.config import settings
from app.services import ServiceResponse


class LocationService:
    def get_location(self, ip: str) -> ServiceResponse:
        api_key = settings.IP_LOCATION_PROVIDER_TOKEN
        location_api_response = None
        default_location = {'ip': 'unknown'}

        if api_key:
            url = f'https://api.ipdata.co/{ip}?api-key={api_key}&fields=ip,city,region,country_name,continent_name'

            try:
                response = requests.get(url=url)
                location_api_response = response.json()
            except Exception:
                return ServiceResponse(is_error=True, description='Can\'t get location')

        location = location_api_response if location_api_response else default_location

        return ServiceResponse(data=location)
