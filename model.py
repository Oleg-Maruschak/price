import sqlite3 as sql
import config



class Connect_db():
    def __init__(self):
        self.con = sql.connect(config.NAME_FILE_DB, check_same_thread=False)

    def insert_for_parsing(self, data, arg=None):
        cur = self.con.cursor()
        if arg == None:
            data.to_sql('search_article', self.con, if_exists='replace', index=False)
            cur.execute('ALTER TABLE search_article ADD exist VARCHAR')
            cur.execute('ALTER TABLE search_article ADD dok VARCHAR')
        else:
            data.to_sql(arg, self.con, if_exists='replace', index=False)
            cur.execute(f'ALTER TABLE {arg} ADD go INT NOT NULL DEFAULT "0"')
            cur.execute(f'ALTER TABLE {arg} ADD err INT NOT NULL DEFAULT 0')

    def select_result(self):
        cur = self.con.cursor()
        cur.execute('SELECT * FROM `search_article`')
        rows = cur.fetchall()
        return list(rows)

    def select_search_article(self):
        cur = self.con.cursor()
        cur.execute('SELECT `rowid`, `Артикул`, `Производитель`, `Производитель exist`, `Производитель dok` FROM `search_article`')
        rows = cur.fetchall()
        return list(rows)

    def select_agent(self):
        cur = self.con.cursor()
        cur.execute('SELECT `rowid`, `user_agent` FROM `user_agent`')
        rows = cur.fetchall()
        return list(rows)

    def select_ip(self):
        cur = self.con.cursor()
        cur.execute('SELECT `rowid`, `ip` FROM `ip` WHERE `err` = 0')
        rows = cur.fetchall()
        return list(rows)

    def update_article(self, id, price, column):
        cur = self.con.cursor()
        cur.execute(f'UPDATE `search_article` SET `{column}` = "{price}" WHERE `rowid` = {int(id)}')

    def update_ip(self, id):
        cur = self.con.cursor()
        cur.execute(f'UPDATE `ip` SET `err` = "1" WHERE `rowid` = {int(id)} ')


    def closes(self):
        self.con.commit()
        self.con.cursor().close()
        self.con.close()
        # self.server.stop()






