from html.parser import HTMLParser

class HTMLTagRemover(HTMLParser):
    """ Courtesy of: https://www.slingacademy.com/article/python-ways-to-remove-html-tags-from-a-string/?utm_content=cmp-true
    """
    def __init__(self):
        super().__init__()
        self.result = []

    def handle_data(self, data):
        self.result.append(data)

    def get_text(self):
        return ''.join(self.result)

def remove_html_tags(text):
    remover = HTMLTagRemover()
    remover.feed(text)
    return remover.get_text()

def log_segnalazioni2message(log_message: str):
    """ """
    return remove_html_tags(log_message.split('- <')[0]).strip()