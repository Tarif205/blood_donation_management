import pandas as pd
from statsmodels.tsa.arima.model import ARIMA
import warnings
import mysql.connector
from datetime import datetime, timedelta

warnings.filterwarnings("ignore")

# Read the CSV file
df = pd.read_csv(r'C:\Users\ASUS_\Downloads\synthetic_blood_demand_data.csv')  # raw string to avoid escape issues
df['Date'] = pd.to_datetime(df['Date'])  # FIXED COLUMN NAME

# Connect to MySQL
conn = mysql.connector.connect(
    host="localhost",
    user="root",
    password="",
    database="blood_donation_management_system"  # ✅ CORRECT
)


cursor = conn.cursor()

from datetime import datetime

# Get today's date string
today = datetime.today().strftime('%Y-%m-%d')

# Check if forecast for today exists for all blood types
cursor.execute("""
    SELECT COUNT(*) FROM blood_type_forecast 
    WHERE Forecast_Date = %s
""", (today,))
count = cursor.fetchone()[0]

if count >= 8:  # Assuming 8 unique blood types
    print(f"✅ Forecast for {today} already exists. Skipping forecast.")
    cursor.close()
    conn.close()
    exit()


# Get unique blood types
blood_types = df['Blood_Type'].unique()

# Set today's date
today = datetime.today().date()


# Forecast for each blood type
for blood_type in blood_types:
    bt_data = df[df['Blood_Type'] == blood_type].set_index('Date').sort_index()  # FIXED INDEX COLUMN
    model = ARIMA(bt_data['Demand'], order=(1, 1, 1))
    model_fit = model.fit()

    # Forecast for the next 7 days from today
    forecast = model_fit.forecast(steps=7)
    forecast_dates = [today + timedelta(days=i) for i in range(1, 8)]  # Forecasting from 2025/05/12 to 2025/05/18

    # Insert forecasts into MySQL
    for date, value in zip(forecast_dates, forecast):
        cursor.execute("""
            INSERT INTO blood_type_forecast (Forecast_Date, Blood_Type, Forecast_Value)
            VALUES (%s, %s, %s)
            ON DUPLICATE KEY UPDATE Forecast_Value = VALUES(Forecast_Value)
        """, (date.strftime('%Y-%m-%d'), blood_type, round(value, 5)))

# Commit the changes to the database
conn.commit()
cursor.close()
conn.close()

print("Forecasting completed and saved to database.")
