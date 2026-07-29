# -*- coding: utf-8 -*-
import mysql.connector
from datetime import date
import logging

# Konfigurasi Logging
logging.basicConfig(
    filename='/var/www/html/monitoring/serverroom/etl.log',
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s'
)

try:
    db = mysql.connector.connect(
        host="localhost",
        user="serveruser",
        password="Server123!",
        database="server_room"
    )
    cursor = db.cursor()
    hari_ini = date.today().strftime('%Y-%m-%d')

    # TRANSFORM: Hanya mengambil suhu, kelembaban, dan alarm
    sql_transform = """
        SELECT 
            AVG(suhu), MAX(suhu), MIN(suhu), 
            AVG(kelembaban), SUM(alarm)
        FROM data_sensor 
        WHERE DATE(waktu) = %s AND suhu BETWEEN 10 AND 50
    """
    cursor.execute(sql_transform, (hari_ini,))
    result = cursor.fetchone()

    if result and result[0] is not None:
        avg_suhu, max_suhu, min_suhu, avg_kelembaban, total_alarm = result
        total_alarm = total_alarm if total_alarm else 0

        # LOAD: Hanya memasukkan kolom suhu, kelembaban, dan alarm ke database
        sql_load = """
            INSERT INTO summary_suhu_harian 
            (tanggal, suhu_rata_rata, suhu_maksimum, suhu_minimum, kelembapan_rata_rata, total_alarm) 
            VALUES (%s, %s, %s, %s, %s, %s)
            ON DUPLICATE KEY UPDATE 
            suhu_rata_rata = VALUES(suhu_rata_rata),
            suhu_maksimum = VALUES(suhu_maksimum),
            suhu_minimum = VALUES(suhu_minimum),
            kelembapan_rata_rata = VALUES(kelembapan_rata_rata),
            total_alarm = VALUES(total_alarm)
        """
        cursor.execute(sql_load, (hari_ini, avg_suhu, max_suhu, min_suhu, avg_kelembaban, total_alarm))
        db.commit()
        
        logging.info(f"ETL sukses untuk tanggal {hari_ini}. Rata-rata Suhu: {avg_suhu}C")
        print("ETL Sukses! Cek log di etl.log")
    else:
        logging.warning(f"Tidak ada data valid yang ditemukan untuk tanggal {hari_ini}.")
        print("Tidak ada data valid untuk diproses.")

except mysql.connector.Error as err:
    logging.error(f"Database error: {err}")
    print(f"Error: {err}")

finally:
    if 'cursor' in locals() and cursor:
        cursor.close()
    if 'db' in locals() and db.is_connected():
        db.close()