# MyFitness - Complete Fitness Tracking App

A production-ready fitness tracking application with Laravel 11 backend, React frontend, and MySQL database.

## Features

- ✅ User Authentication (Register/Login/Logout)
- ✅ Multi-user Support (each user has isolated data)
- ✅ Training Plan Management (create, edit, delete, track)
- ✅ Real-time Workout Tracking (sets, reps, weight, RIR)
- ✅ Nutrition Logging (calories, macros, meals)
- ✅ Progress Tracking (weight, measurements, charts)
- ✅ User Profile Management
- ✅ Responsive Design (mobile, tablet, desktop)
- ✅ JWT Authentication
- ✅ CORS Enabled

## Tech Stack

**Backend:**
- Laravel 11
- PHP 8.4
- MySQL Database

**Frontend:**
- React with TypeScript
- Tailwind CSS
- Axios for API calls

**Hosting:**
- Hetzner Server (Ubuntu Linux)
- cPanel Control Panel
- Nginx/Apache
- SSL Certificate

## Project Structure

```
fitness-app/
├── backend/              # Laravel API
│   ├── app/
│   ├── routes/
│   ├── database/
│   ├── .env.example
│   └── composer.json
├── frontend/             # React App
│   ├── src/
│   ├── public/
│   ├── package.json
│   └── .env.example
├── database/             # SQL Schema
│   └── schema.sql
├── DEPLOYMENT.md         # Deployment Guide
├── API_DOCS.md          # API Documentation
└── README.md
```

## Quick Start

### Backend Setup

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

### Frontend Setup

```bash
cd frontend
npm install
npm start
```

## Database Configuration

Configure your database credentials in `backend/.env` (see `.env.example`).

## API Endpoints

### Authentication
- `POST /api/auth/register` - Register new user
- `POST /api/auth/login` - Login user
- `POST /api/auth/logout` - Logout user
- `GET /api/auth/me` - Get current user

### Training Plans
- `GET /api/training-plans` - List user's plans
- `POST /api/training-plans` - Create plan
- `GET /api/training-plans/{id}` - Get plan details
- `PUT /api/training-plans/{id}` - Update plan
- `DELETE /api/training-plans/{id}` - Delete plan

### Workouts
- `POST /api/workouts/start` - Start workout session
- `POST /api/workouts/{id}/exercise` - Log exercise
- `POST /api/workouts/{id}/end` - End workout
- `GET /api/workouts/history` - Get workout history

### Nutrition
- `POST /api/nutrition/log` - Log meal
- `GET /api/nutrition/{date}` - Get daily nutrition

### Progress
- `POST /api/measurements` - Add measurement
- `GET /api/progress` - Get progress data

## Deployment

See [DEPLOYMENT.md](./DEPLOYMENT.md) for complete step-by-step deployment guide.

### Quick Deploy Steps

1. Create subdomain `myfitness.deinedomain.com` in cPanel
2. Upload backend to `/public_html/myfitness/api`
3. Upload frontend build to `/public_html/myfitness`
4. Configure `.env` files
5. Run migrations
6. Test at `https://myfitness.deinedomain.com`

## Environment Configuration

### Backend (.env)
```
APP_URL=https://api.yourdomain.com
DB_HOST=your-db-host
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
CORS_ALLOWED_ORIGINS=https://myfitness.yourdomain.com
```

### Frontend (.env)
```
REACT_APP_API_URL=https://myfitness.deinedomain.com/api
```

## Database Schema

The app uses the following tables:
- `users` - User accounts
- `training_plans` - Training programs
- `workout_sessions` - Individual workouts
- `exercise_logs` - Set/rep/weight data
- `nutrition_logs` - Meal tracking
- `user_measurements` - Body metrics
- `progress_photos` - Progress images

## Features in Detail

### Training Plans
- Create multiple training plans (PPL, UL, Full Body, Custom)
- Set duration, template type, description
- Track active status

### Workout Tracking
- Start workout session
- Log exercises in real-time
- Track sets, reps, weight, RIR
- Record energy level and notes
- View complete workout history

### Nutrition
- Log meals by type (Breakfast, Lunch, Dinner, Snack, Post-Workout)
- Auto-calculate daily macros
- Weekly nutrition reports

### Progress
- Weekly weigh-ins
- Body measurements (chest, waist, hips, arms, thighs)
- Progress photos
- Charts and graphs
- Historical data comparison

## Security

- JWT-based authentication
- Password hashing with bcrypt
- CORS configuration
- Input validation
- SQL injection prevention
- Rate limiting on auth endpoints
- HTTPS enforced

## Browser Support

- Chrome/Edge (latest)
- Firefox (latest)
- Safari (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

## Troubleshooting

### Database Connection Error
- Verify credentials in `.env`
- Check MySQL port (3306)
- Confirm host accessibility

### CORS Issues
- Update `CORS_ALLOWED_ORIGINS` in backend `.env`
- Clear browser cache
- Check API URL in frontend `.env`

### File Upload Issues
- Check folder permissions
- Verify disk space
- Ensure PHP upload limits

## Support & Documentation

- API Documentation: [API_DOCS.md](./API_DOCS.md)
- Deployment Guide: [DEPLOYMENT.md](./DEPLOYMENT.md)
- Database Schema: [database/schema.sql](./database/schema.sql)

## License

MIT License - feel free to use for personal projects

## Author

Yusuf Rasulian

---

**Questions?** Check the DEPLOYMENT.md or API_DOCS.md files first!
