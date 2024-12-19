-- Step 1: Create the organisationTable without the teamLeaderID foreign key initially
CREATE TABLE organisationTable (
    organisationID INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    teamLeaderID INT
);

-- Step 2: Create the userTable without the organisationID foreign key initially
CREATE TABLE userTable (
    userID INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    forename VARCHAR(50) NOT NULL,
    surname VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    organisationID INT
);

-- Step 3: Add the foreign key constraint on teamLeaderID in organisationTable to reference userTable
ALTER TABLE organisationTable
ADD CONSTRAINT fk_teamLeaderID FOREIGN KEY (teamLeaderID) REFERENCES userTable(userID);

-- Step 4: Add the foreign key constraint on organisationID in userTable to reference organisationTable
ALTER TABLE userTable
ADD CONSTRAINT fk_organisationID FOREIGN KEY (organisationID) REFERENCES organisationTable(organisationID);

-- Step 5: Create the episodesTable
CREATE TABLE episodesTable (
    episodeID INT PRIMARY KEY AUTO_INCREMENT,
    episodeName VARCHAR(100) NOT NULL
);

-- Step 6: Create the questionTable with four answer options and a foreign key to episodesTable
CREATE TABLE questionTable (
    questionID INT PRIMARY KEY AUTO_INCREMENT,
    episodeID INT NOT NULL,
    questionText TEXT NOT NULL,
    answerA VARCHAR(255) NOT NULL,
    answerB VARCHAR(255) NOT NULL,
    answerC VARCHAR(255) NOT NULL,
    answerD VARCHAR(255) NOT NULL,
    correctAnswer CHAR(1) CHECK (correctAnswer IN ('A', 'B', 'C', 'D')),
    FOREIGN KEY (episodeID) REFERENCES episodesTable(episodeID)
);

-- Step 7: Create the userProgressTable with a foreign key to userTable
CREATE TABLE userProgressTable (
    userID INT PRIMARY KEY,
    storyCompleted BOOLEAN DEFAULT FALSE,
    quizCompleted BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (userID) REFERENCES userTable(userID)
);

-- Step 8: Create the leaderboardTable with scores for six quizzes and a foreign key to userTable
CREATE TABLE leaderboardTable (
    userID INT PRIMARY KEY,
    quiz1Score INT DEFAULT 0,
    quiz2Score INT DEFAULT 0,
    quiz3Score INT DEFAULT 0,
    quiz4Score INT DEFAULT 0,
    quiz5Score INT DEFAULT 0,
    quiz6Score INT DEFAULT 0,
    FOREIGN KEY (userID) REFERENCES userTable(userID)
);


CREATE TABLE storyTable (
    storyID INT AUTO_INCREMENT PRIMARY KEY,
    episodeID INT NOT NULL,
    storyText TEXT NOT NULL,
    storyQuestion TEXT NOT NULL,
    answerA TEXT NOT NULL,
    answerB TEXT NOT NULL,
    answerC TEXT NOT NULL,
    correctAnswer CHAR(1) CHECK (correctAnswer IN ('A', 'B', 'C')), -- Only allows 'A', 'B', or 'C'
    FOREIGN KEY (episodeID) REFERENCES episodesTable(episodeID) ON DELETE CASCADE
);




CREATE TABLE episodeCompletionLog (
    logID INT PRIMARY KEY AUTO_INCREMENT,
    userID INT NOT NULL,
    episodeID INT NOT NULL,
    startTime DATETIME NOT NULL,
    endTime DATETIME NOT NULL,
    durationInSeconds INT GENERATED ALWAYS AS (TIMESTAMPDIFF(SECOND, startTime, endTime)) STORED,
    FOREIGN KEY (userID) REFERENCES userTable(userID)
);

CREATE TABLE storyCompletionLog (
    logID INT PRIMARY KEY AUTO_INCREMENT,
    userID INT NOT NULL,
    storyID INT NOT NULL,
    startTime DATETIME NOT NULL,
    endTime DATETIME NOT NULL,
    durationInSeconds INT GENERATED ALWAYS AS (TIMESTAMPDIFF(SECOND, startTime, endTime)) STORED,
    FOREIGN KEY (userID) REFERENCES userTable(userID)
);


CREATE TABLE employeeStatus (
    statusID INT PRIMARY KEY AUTO_INCREMENT,
    userID INT NOT NULL,
    isActive BOOLEAN NOT NULL DEFAULT 1, -- 1 = Active, 0 = Inactive
    updatedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (userID) REFERENCES userTable(userID)
);

CREATE TABLE employeeActivityLog (
    logID INT PRIMARY KEY AUTO_INCREMENT,
    userID INT NOT NULL,
    activityDate DATETIME NOT NULL,
    activityType VARCHAR(50) NOT NULL, -- e.g., "Login", "Logout", "Task Completed"
    FOREIGN KEY (userID) REFERENCES userTable(userID)
);


ALTER TABLE employeeStatus ADD CONSTRAINT UNIQUE (userID);
ALTER TABLE storyCompletionLog MODIFY COLUMN endTime DATETIME NULL;
ALTER TABLE episodeCompletionLog MODIFY COLUMN endTime DATETIME NULL;
