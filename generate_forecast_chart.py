import mysql.connector
import matplotlib.pyplot as plt
import pandas as pd
import os

# Connect to the database
conn = mysql.connector.connect(
    host="localhost",
    user="root",
    password="",
    database="blood_donation_management_system"
)
cursor = conn.cursor()

# Query forecast data
query = """
    SELECT Forecast_Date, Blood_Type, Forecast_Value
    FROM blood_type_forecast
    WHERE Forecast_Date >= CURDATE()
    ORDER BY Forecast_Date, Blood_Type
"""
cursor.execute(query)
rows = cursor.fetchall()
df = pd.DataFrame(rows, columns=['Date', 'Blood_Type', 'Forecast'])

# Close DB connection
cursor.close()
conn.close()

# Plot
plt.figure(figsize=(12, 6))
for blood_type in df['Blood_Type'].unique():
    sub_df = df[df['Blood_Type'] == blood_type]
    plt.plot(pd.to_datetime(sub_df['Date']), sub_df['Forecast'], label=blood_type)

plt.title("7-Day Blood Demand Forecast")
plt.xlabel("Date")
plt.ylabel("Forecast Value")
plt.legend()
plt.tight_layout()

# Save chart image
output_path = r"c:/xampp/htdocs/Project_cse370/blood_donation_management/forecast_chart.png"
plt.savefig(output_path)
