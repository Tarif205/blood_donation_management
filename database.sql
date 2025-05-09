#Schema_structure
-- USER table
CREATE TABLE USER (
  ID INT PRIMARY KEY AUTO_INCREMENT,
  Name VARCHAR(100),
  Phone_Number VARCHAR(20),
  Age INT,
  Email VARCHAR(100) UNIQUE,
  Password VARCHAR(100),
  Is_Available BOOLEAN DEFAULT 1,
  Is_Patient BOOLEAN DEFAULT FALSE,
  Is_Donor BOOLEAN DEFAULT FALSE,
  Is_Staff BOOLEAN DEFAULT FALSE
);

-- User Blood Types
CREATE TABLE User_Blood_Type (
  USER_ID INT,
  Blood_Type VARCHAR(10),
  FOREIGN KEY (USER_ID) REFERENCES USER(ID)
    ON DELETE CASCADE
    ON UPDATE CASCADE
);

-- User Health Issues
CREATE TABLE User_Health_Issues (
  USER_ID INT,
  Health_Issues TEXT,
  FOREIGN KEY (USER_ID) REFERENCES USER(ID)
    ON DELETE CASCADE
    ON UPDATE CASCADE
);

-- Donation History
CREATE TABLE Donation_History (
  Donation_ID INT PRIMARY KEY AUTO_INCREMENT,
  Donor_Name VARCHAR(100),
  Last_Donation_Date DATE,
  Donor_ID INT,
  FOREIGN KEY (Donor_ID) REFERENCES USER(ID)
    ON DELETE SET NULL
    ON UPDATE CASCADE
);

-- Donated Blood Type
CREATE TABLE Donated_Blood_Type (
  Donation_ID INT,
  Blood_Type VARCHAR(10),
  FOREIGN KEY (Donation_ID) REFERENCES Donation_History(Donation_ID)
    ON DELETE CASCADE
    ON UPDATE CASCADE
);

-- Blood Request
CREATE TABLE Blood_Request (
  Request_ID INT PRIMARY KEY AUTO_INCREMENT,
  Patient_Name VARCHAR(100),
  Patient_Phone_Number VARCHAR(20),
  Time_Stamp DATETIME DEFAULT CURRENT_TIMESTAMP,
  Status VARCHAR(50),
  Staff_ID INT,
  Patient_ID INT,
  FOREIGN KEY (Staff_ID) REFERENCES USER(ID)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  FOREIGN KEY (Patient_ID) REFERENCES USER(ID)
    ON DELETE SET NULL
    ON UPDATE CASCADE
);

-- Requested Blood Types
CREATE TABLE Requested_Blood_Type (
  Request_ID INT,
  Blood_Type VARCHAR(10),
  FOREIGN KEY (Request_ID) REFERENCES Blood_Request(Request_ID)
    ON DELETE CASCADE
    ON UPDATE CASCADE
);

-- Eligibility Check
CREATE TABLE Eligibility_Check (
  Donor_ID INT,
  Donation_ID INT,
  Request_ID INT,
  Blood_Type varchar(10),
  is_eligible BOOLEAN DEFAULT FALSE,
  is_accepted BOOLEAN DEFAULT FALSE,
    is_approved BOOLEAN DEFAULT FALSE,
    Approved_By INT
  FOREIGN KEY (Donor_ID) REFERENCES USER(ID)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  FOREIGN KEY (Donation_ID) REFERENCES Donation_History(Donation_ID)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  FOREIGN KEY (Request_ID) REFERENCES Blood_Request(Request_ID)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  FOREIGN KEY (Approved_By) REFERENCES USER(ID)
);

-- Sent Notification
CREATE TABLE Sent_Notification (
  Donor_ID INT,
  Request_ID INT,
  FOREIGN KEY (Donor_ID) REFERENCES USER(ID)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  FOREIGN KEY (Request_ID) REFERENCES Blood_Request(Request_ID)
    ON DELETE CASCADE
    ON UPDATE CASCADE
);

-- Access
CREATE TABLE Access (
  Staff_ID INT,
  Donation_ID INT,
  FOREIGN KEY (Staff_ID) REFERENCES USER(ID)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  FOREIGN KEY (Donation_ID) REFERENCES Donation_History(Donation_ID)
    ON DELETE CASCADE
    ON UPDATE CASCADE
);

-- External Source
CREATE TABLE External_Source (
  ID INT PRIMARY KEY AUTO_INCREMENT,
  Name VARCHAR(100),
  Date_Requested DATE,
  Email VARCHAR(100),
  Availability BOOLEAN,
  Contact_Number VARCHAR(20),
  City_Zip VARCHAR(20),
  Street_Number VARCHAR(20),
  Area VARCHAR(100)
);

-- External Source Blood Type
CREATE TABLE External_Source_Blood_Type (
  External_Source_ID INT,
  Blood_Type VARCHAR(10),
  FOREIGN KEY (External_Source_ID) REFERENCES External_Source(ID)
    ON DELETE CASCADE
    ON UPDATE CASCADE
);

-- Connects
CREATE TABLE Connects (
  Staff_ID INT,
  External_Source_ID INT,
  FOREIGN KEY (Staff_ID) REFERENCES USER(ID)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  FOREIGN KEY (External_Source_ID) REFERENCES External_Source(ID)
    ON DELETE CASCADE
    ON UPDATE CASCADE
);