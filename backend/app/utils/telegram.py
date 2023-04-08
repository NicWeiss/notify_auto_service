import requests

from app.core.config import settings


class Telegram:
    @staticmethod
    def send(recipient: str, title: str, text: str) -> bool:
        url = f'https://api.telegram.org/bot{settings.TELERGAM_BOT_TOKEN}/sendMessage'
        payload = {'chat_id': recipient, 'text': f'{title} \n {text}'}
        response = requests.post(url, json=payload)

        return response.status_code == 200
