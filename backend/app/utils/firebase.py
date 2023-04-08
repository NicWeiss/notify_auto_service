import requests

from app.core.config import settings


class FireBase:
    @staticmethod
    def send_push(recipient: str, title: str, text: str) -> bool:
        url = 'https://fcm.googleapis.com/fcm/send'
        payload = {
            'to': recipient,
            'notification': {
                'title': title,
                'body': text,
                'icon': 'ic_launcher',
                'color': '#de6c20'
            }
        }
        headers = {
            'Content-Type': 'application/json',
            'Authorization': f'key={settings.FCM_SERVER_KEY}'
        }
        response = requests.post(url, headers=headers, json=payload)

        return response.status_code == 200
