# St. Antony's Karunai Illam Donation API

A simple API backend for handling donations for St. Antony's Karunai Illam.

## Setup Instructions

### Local Development

1. Clone this repository
2. Install dependencies:
   ```
   composer install
   ```
3. Set up MongoDB locally or use MongoDB Atlas
4. Create a `.env` file with your MongoDB connection string:
   ```
   MONGODB_URI=mongodb://localhost:27017/karunai_illam
   ```
5. Start a local PHP server:
   ```
   php -S localhost:8000
   ```

### Deployment on Render

1. Create a new Web Service on Render and link to your GitHub repository
2. Configure the service:
   - Environment: PHP
   - Build Command: `composer install`
   - Start Command: `vendor/bin/heroku-php-apache2`
3. Add the environment variable:
   - MONGODB_URI = your MongoDB Atlas connection string

## API Endpoints

### GET /
Status check endpoint that returns the API status.

### POST /
Submit a donation with the following required fields:
- firstName
- lastName
- email
- phone
- donationFor
- amount

## Response Format

Successful submission:
```json
{
  "success": true,
  "message": "Donation submitted successfully",
  "reference": "SAKI-2025031312"
}
```

Error response:
```json
{
  "success": false,
  "message": "Error description"
}
```