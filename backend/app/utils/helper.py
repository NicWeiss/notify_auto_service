import re


def uncamelcase(name: str) -> str:
    name = re.sub('(.)([A-Z][a-z])', r'\1_\2', name)
    return re.sub('([a-z0-9])([A-Z])', r'\1_\2', name).lower()
