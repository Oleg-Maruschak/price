import random
import requests as rq
import requests.exceptions as error
import sys
import pandas as pd
import config as cf
import function as f
from bs4 import BeautifulSoup
from errors import Err


class EXIST:
    def __init__(self):
        self.countListAll = 0
        self.countNow = 0
        self.DataFrame = {'Артикул': [], 'Наименование': [], 'Производитель': [], 'Exist': []}
        self.listRezult = None

    def get_article(self, data, agent, ip, PRICECART):
        self.listRezult = {'article': [], 'brand': [], 'price': [], 'id': []}
        url = f'{cf.URLEXIST}{str(data[1]).replace(" ", "")}{cf.URL2EXIST}'
        try:
            get_url = rq.get(url, headers=agent, proxies=ip, timeout=10)
        except (error.ConnectTimeout, error.Timeout, error.ReadTimeout):
            return 'timeout'
        except (error.ProxyError, error.InvalidURL):
            return 'ip'
        except Exception as val:
            Err('exist.txt', str(sys.exc_info()[0]), str(val))
            return 'err'
        decodJson = get_url.json()
        products_by_ware_num_just = decodJson['result']['products_by_ware_num_just']
        products = decodJson['result']['products']
        self.getListArticle(products_by_ware_num_just)
        self.getListArticle(products)
        new = pd.DataFrame(self.listRezult)
        new2 = new.loc[new['brand'].replace(to_replace=r' ', value='', regex=True).str.upper() == data[3].upper().replace(' ', '')]
        if new2.empty:
            return 'None'
        else:
            i = new2.index[0]
            if PRICECART:
                id= new2.loc[i, 'id']
                price = self.get_price_article(agent, ip, id)
                return price
            else:
                return new2.loc[i, 'price']

    def get_price_article(self, agent, ip, id):
        url = f'{cf.URL_EXIST_ID}{id}'
        try:
            get_url = rq.get(url, headers=agent, proxies=ip, timeout=10)
        except (error.ConnectTimeout, error.Timeout, error.ReadTimeout):
            return 'timeout'
        except (error.ProxyError, error.InvalidURL):
            return 'ip'
        except Exception as val:
            Err('exist.txt', str(sys.exc_info()[0]), str(val))
            return 'err'
        get_html = BeautifulSoup(get_url.text, 'html.parser')
        try:
            price = get_html.find('div', class_='SearchResultsGridstyle__SearchResultsPrice-sc-1ibh0zg-15 jiYhBu').find('span').text
            return price
        except Exception:
            return 'html'

    def getListArticle(self, list):
        try:
            for i, e in enumerate(list):
                self.listRezult['article'].append(e['upc'])
                self.listRezult['brand'].append(e['trademark']['description'])
                self.listRezult['id'].append(e['prag_id'])
                if e['price'] == None:
                    self.listRezult['price'].append('0')
                else:
                    self.listRezult['price'].append(e['price']['price'])

        except Exception:
            self.listRezult['article'].append('None')
            self.listRezult['brand'].append('None')
            self.listRezult['price'].append('None')
            self.listRezult['id'].append('None')


