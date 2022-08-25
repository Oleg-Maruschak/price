import requests as rq
import requests.exceptions as error
from errors import Err
import config as cf
from bs4 import BeautifulSoup
import sys


class DOK:
    def format_string(self, data):
        name = str(data).replace(' ', '').replace('-', '').replace('/', '').replace('*', '').replace('.', '').replace(',', '')
        name = name.upper()
        return name

    def get_article(self, data, agent, ip, PRICECART):
        url1 = f'{cf.URLDOK}{str(data[1]).replace(" ", "")}'
        try:
            get_url = rq.get(url1, headers=agent, proxies=ip, timeout=10)
        except (error.ConnectTimeout, error.Timeout, error.ReadTimeout):
            return 'timeout'
        except (error.ProxyError, error.InvalidURL):
            return 'ip'
        except Exception as val:
            Err('dok.txt', str(sys.exc_info()[0]), str(val))
            return 'err'

        get_html = BeautifulSoup(get_url.text, 'html.parser')

        try:
            list_article = get_html.find_all('a', class_='search-result-shortlist-item__title')
        except Exception:
            return 'html'

        for i in list_article:
            brandAP = self.format_string(data[4])
            artAP = self.format_string(data[1])
            artDK = self.format_string(i.text)
            brandDK = self.format_string(artDK[:artDK.find(artAP)])
            if brandDK == brandAP:
                url2 = f'{cf.URLDOK2}{i["data-clear-url"]}'

                try:
                    get_url2 = rq.get(url2, headers=agent, proxies=ip, timeout=10)
                except (error.ConnectTimeout, error.Timeout, error.ReadTimeout):
                    return 'timeout'
                except (error.ProxyError, error.InvalidURL):
                    return 'ip'
                except Exception as val:
                    Err('dok.txt', str(sys.exc_info()[0]), str(val))
                    return 'err'

                get_html2 = BeautifulSoup(get_url2.text, 'html.parser')

                try:
                    price = get_html2.find('div', class_='search-result__price').text.replace(' ', '').replace('\n', '').replace('грн', '')
                    return price
                except Exception:
                    return 'html'
            else:
                 continue



