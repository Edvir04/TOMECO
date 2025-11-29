const express = require('express');
const bodyParser = require('body-parser');
const { Pool } = require('pg');

const app = express();
const PORT = process.env.PORT || 3000;

// Middleware
app.use(bodyParser.json({limit: '50mb'}));


// PostgreSQL configuration
const pool = new Pool({
  user: 'postgres',
  host: 'localhost',
  database: 'capstone_gigs',
  password: '1234',
  port: 5432, 
});


const { v4: uuidv4 } = require('uuid');
const bcrypt = require('bcrypt');

// Register a new user in the database
app.post('/register', async (req, res) => {
    try {
        const {
            username,
            email,
            password,
            gender,
            birthdate,
            address,
            phone,
        } = req.body;

        // Generate a random device token
        const deviceToken = uuidv4(); 

        // Hash the password
        const hashedPassword = await bcrypt.hash(password, 10); // 10 is the saltRounds

        const client = await pool.connect();
        const query = `
        INSERT INTO E_Ticket_Employees (username, email, password, gender, birthdate, address, phone, devicetoken, created_at)
        VALUES ($1, $2, $3, $4, $5, $6, $7, $8, NOW())
      `;

        const values = [
            username,
            email,
            hashedPassword, // Use hashedPassword instead of password
            gender,
            birthdate,
            address,
            phone,
            deviceToken,
        ];
        await client.query(query, values);
        client.release();

        res.status(201).send('User registered successfully');
    } catch (error) {
        console.error('Error registering user', error);
        res.status(500).send('Internal Server Error');
    }
});

// Handle the login request
app.post('/login', async (req, res) => { 
  try {
      const { email, password } = req.body;

      const client = await pool.connect();
      const query = 'SELECT * FROM E_Ticket_Employees WHERE email = $1';
      const result = await client.query(query, [email]);

      if (result.rows.length === 0) {
          res.status(401).send('Invalid email or password');
          client.release();
          return;
      }

      const user = result.rows[0];
      const isPasswordValid = await bcrypt.compare(password, user.password);

      if (!isPasswordValid) {
          res.status(401).send('Invalid email or password');
          client.release();
          return;
      }

      // Fetch all data for the logged-in user
      const userDataQuery = 'SELECT * FROM E_Ticket_Employees WHERE id = $1';
      const userDataResult = await client.query(userDataQuery, [user.id]);

      // Log fetched user data to the console
      console.log('Fetched user data:', userDataResult.rows);

      client.release();

      // Send both user and user data back to the client
      res.status(200).json({ user: user, userData: userDataResult.rows });
  } catch (error) {
      console.error('Error logging in user', error);
      res.status(500).send('Internal Server Error');
  }
});

// Handle the update function on profile
app.put('/updateProfile', async (req, res) => {
  const updatedUser = req.body;

  // Check if the password is provided and not empty
  if (updatedUser.password) {
    // Retrieve the hashed password of the user from the database based on user's ID or email
    try {
      const result = await pool.query('SELECT password FROM E_Ticket_Employees WHERE id = $1', [updatedUser.id]);
      const storedPassword = result.rows[0].password;

      // Log the updatedUser.password and storedPassword for debugging
      console.log('Updated password:', updatedUser.password);
      console.log('Stored password:', storedPassword);

      // Directly compare the provided password with the stored hashed password
      if (updatedUser.password === storedPassword) {
        // If passwords match, update user profile without changing the password
        updateUserProfileWithoutPassword(updatedUser, res);
      } else {
        // If passwords don't match, hash the provided password and update the profile
        updateUserProfileWithPassword(updatedUser, res);
      }
    } catch (error) {
      console.error('Error:', error);
      return res.status(500).json({ error: 'Internal Server Error' });
    }
  } else {
    // Update the user profile in the database without password update
    updateUserProfileWithoutPassword(updatedUser, res);
  }
});

// Function to update user profile without changing the password
function updateUserProfileWithoutPassword(updatedUser, res) {
  const query = 'UPDATE E_Ticket_Employees SET username = $1, email = $2, gender = $3, birthdate = $4, address = $5, phone = $6, bloodtype = $7, updated_at = NOW() WHERE id = $8 RETURNING *';
  const values = [
    updatedUser.username,
    updatedUser.email,
    updatedUser.gender,
    updatedUser.birthdate,
    updatedUser.address,
    updatedUser.phone,
    updatedUser.bloodtype,
    updatedUser.id,
  ];

  pool.query(query, values)
    .then(result => {
      const updatedUser = result.rows[0];
      res.status(200).json(updatedUser);
    })
    .catch(err => {
      console.error(err);
      res.status(500).json({ error: 'Failed to update profile' });
    });
}

// Function to update user profile with changing the password
async function updateUserProfileWithPassword(updatedUser, res) {
  try {
    // Hash the provided password
    const hashedPassword = await bcrypt.hash(updatedUser.password, 10);
    updatedUser.password = hashedPassword;

    const query = 'UPDATE E_Ticket_Employees SET username = $1, email = $2, password = $3, gender = $4, birthdate = $5, address = $6, phone = $7, bloodtype = $8, updated_at = NOW() WHERE id = $9 RETURNING *';
    const values = [
      updatedUser.username,
      updatedUser.email,
      updatedUser.password,
      updatedUser.gender,
      updatedUser.birthdate,
      updatedUser.address,
      updatedUser.phone,
      updatedUser.bloodtype,
      updatedUser.id,
    ];

    pool.query(query, values)
      .then(result => {
        const updatedUser = result.rows[0];
        res.status(200).json(updatedUser);
      })
      .catch(err => {
        console.error(err);
        res.status(500).json({ error: 'Failed to update profile' });
      });
  } catch (error) {
    console.error('Error:', error);
    return res.status(500).json({ error: 'Internal Server Error' });
  }
}

// Handle to delete user profile
app.delete('/deleteAccount/:userId', async (req, res) => {
  const userId = req.params.userId;

  try {
    // Delete the user from the donors_users table
    const query = 'DELETE FROM E_Ticket_Employees WHERE id = $1';
    await pool.query(query, [userId]);

    res.status(200).json({ message: 'Account deleted successfully' });
  } catch (error) {
    console.error('Error deleting account:', error);
    res.status(500).json({ error: 'Failed to delete account' });
  }
});

// POST endpoint to handle form submission for add request
app.post('/ticket_issuance', async (req, res) => {
  let {
    driversName, Address, driversPermit, pltNumber, crNumber, orNumber,
    make, model, type, year, owner, ownerAddress, Place, Accident,
    apprehendingOfficer, tomecoID, userID, requiredDate, image,
    prof, Np, Sp, Violation1, Violation2, Violation3, Violation4,
    Violation5, Violation6, Violation7, Violation8, Violation9, 
    Violation10, Violation11, Violation12, Admitted, underProtest
  } = req.body;

  try {
    if (prof === true) {
      prof = "Professional";
    }
    if (Np === true) {
      Np = "Non-Professional";
    }
    if (Sp === true) {
      Sp = "Student Permit";
    }
    if (Admitted === true) {
      Admitted = "Admitted";
    }
    if (underProtest === true) {
      underProtest = "Under Protest";
    }
    if (Violation1 === true) {
      Violation1 = "Driving Without D/L";
    }
    if (Violation2 === true) {
      Violation2 = "Unregistered Vehicle";
    }
    if (Violation3 === true) {
      Violation3 = "No Helmet";
    }
    if (Violation4 === true) {
      Violation4 = "Illegal Parking";
    }
    if (Violation5 === true) {
      Violation5 = "Disregarding Traffic Sign";
    }
    if (Violation6 === true) {
      Violation6 = "Truck Ban";
    }
    if (Violation7 === true) {
      Violation7 = "Obstruction";
    }
    if (Violation8 === true) {
      Violation8 = "Defective HeadLight";
    }
    if (Violation9 === true) {
      Violation9 = "Operating Along National Highway";
    }
    if (Violation10 === true) {
      Violation10 = 'Violation to CO # 2007-10-31 "The Anti-Littering Ordinance."';
    }
    if (Violation11 === true) {
      Violation11 = 'Violation to CO # 2009-10-160 "The Anti-Smoking Ordinance."';
    }
    if (Violation12 === true) {
      Violation12 = 'Violation to CO # 2007-10-66 "The Anti-urinating and Defecating Ordinance."';
    }    

    const imageBuffer = image ? Buffer.from(image, 'base64') : null;

    const newRequest = await pool.query(
      `INSERT INTO ticket_issuance 
      (created_at, drivers_name, address, drivers_permit, plt_number, cr_number, or_number,
       make, model, type, year, owner, owner_address, place, accident,
       apprehending_officer, tomeco_id, user_id, required_date, image,
       prof, np, sp, violation1, violation2, violation3, violation4,
       violation5, violation6, violation7, violation8, violation9, 
       violation10, violation11, violation12, admitted, under_protest) 
       VALUES (NOW(), $1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, 
       $14, $15, $16, $17, $18, $19, $20, $21, $22, $23, $24, $25, $26, $27, 
       $28, $29, $30, $31, $32, $33, $34, $35,$36) RETURNING *`,
      
      [driversName, Address, driversPermit, pltNumber, crNumber, orNumber,
       make, model, type, year, owner, ownerAddress, Place, Accident,
       apprehendingOfficer, tomecoID, userID, requiredDate, imageBuffer,
       prof, Np, Sp, Violation1, Violation2, Violation3, Violation4,
       Violation5, Violation6, Violation7, Violation8, Violation9, 
       Violation10, Violation11, Violation12, Admitted, underProtest]
    );

    res.json(newRequest.rows[0]);
  } catch (err) {
    console.error(err.message);
    res.status(500).json({ error: 'Server error' });
  }
});



// Route to fetch all data from the blood_requests_own table
app.get('/ticket_issued_own', async (req, res) => {
  try {
    const client = await pool.connect();
    const result = await client.query('SELECT * FROM ticket_issuance ORDER BY created_at DESC'); // Order by creation date in descending order
    const data = result.rows;
    client.release(); // Release the client back to the pool
    res.json(data);
  } catch (error) {
    console.error('Error fetching data:', error);
    res.status(500).json({ message: 'Internal server error' });
  }
});


// Route to fetch all data from the events table
app.get('/events', async (req, res) => {
  try {
    const client = await pool.connect();
    const result = await client.query('SELECT * FROM events');
    const data = result.rows;
    client.release(); 
    res.json(data);
  } catch (error) {
    console.error('Error fetching data:', error);
    res.status(500).json({ message: 'Internal server error' });
  }
});

// Start server
app.listen(PORT, () => {
  console.log(`Server is running on port ${PORT}`);
});
