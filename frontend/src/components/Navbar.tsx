import React from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

export default function Navbar() {
  const { user, logout } = useAuth();
  const navigate = useNavigate();

  const handleLogout = () => {
    logout();
    navigate('/login');
  };

  return (
    <nav className="bg-blue-600 text-white shadow-lg">
      <div className="container mx-auto px-4 py-4">
        <div className="flex justify-between items-center">
          <Link to="/dashboard" className="text-2xl font-bold">
            MyFitness
          </Link>

          <div className="flex gap-6 items-center">
            <Link to="/dashboard" className="hover:text-blue-200">
              Dashboard
            </Link>
            <Link to="/training-plans" className="hover:text-blue-200">
              Training Plans
            </Link>
            <Link to="/workout-tracker" className="hover:text-blue-200">
              Tracker
            </Link>
            <Link to="/nutrition" className="hover:text-blue-200">
              Nutrition
            </Link>
            <Link to="/progress" className="hover:text-blue-200">
              Progress
            </Link>
            <Link to="/profile" className="hover:text-blue-200">
              {user?.name}
            </Link>
            {user?.is_admin && (
              <Link to="/admin" className="bg-red-700 px-3 py-1 rounded text-sm font-medium hover:bg-red-800">
                Admin
              </Link>
            )}
            <button
              onClick={handleLogout}
              className="bg-red-600 px-4 py-2 rounded hover:bg-red-700"
            >
              Logout
            </button>
          </div>
        </div>
      </div>
    </nav>
  );
}
