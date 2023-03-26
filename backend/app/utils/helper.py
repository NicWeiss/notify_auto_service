import re
import subprocess


def uncamelcase(name: str) -> str:
    name = re.sub('(.)([A-Z][a-z])', r'\1_\2', name)
    return re.sub('([a-z0-9])([A-Z])', r'\1_\2', name).lower()


def html_to_text(html):
    args = ('lynx', '-assume-charset=UTF-8', '-display-charset=UTF-8', '-dump', '-stdin')
    process = subprocess.Popen(args, stdin=subprocess.PIPE, stdout=subprocess.PIPE)
    return process.communicate(html.encode('utf-8'))[0].decode('utf-8')
