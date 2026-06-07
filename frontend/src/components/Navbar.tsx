import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

export default function Navbar() {
  const { user, logout } = useAuth();
  const navigate = useNavigate();
  const [menuOpen, setMenuOpen] = useState(false);

  const handleLogout = () => { logout(); navigate('/login'); };

  const links = [
    { to: '/dashboard', label: 'Dashboard' },
    { to: '/training-plans', label: 'Pläne' },
    { to: '/workout-tracker', label: 'Tracker' },
    { to: '/progress', label: 'Fortschritt' },
    { to: '/nutrition', label: 'Ernährung' },
    { to: '/profile', label: user?.name || 'Profil' },
  ];

  return (
    <nav className="bg-blue-600 text-white shadow-lg">
      <div className="container mx-auto px-4 py-3">
        <div className="flex justify-between items-center">
          <Link to="/dashboard" className="text-xl font-bold">MyFitness</Link>

          {/* Desktop */}
          <div className="hidden md:flex gap-4 items-center">
            {links.map(l => (
              <Link key={l.to} to={l.to} className="hover:text-blue-200 text-sm">{l.label}</Link>
            ))}
            {user?.is_admin && (
              <Link to="/admin" className="bg-red-700 px-3 py-1 rounded text-sm font-medium hover:bg-red-800">Admin</Link>
            )}
            <button onClick={handleLogout} className="bg-red-600 px-3 py-1.5 rounded text-sm hover:bg-red-700">Logout</button>
          </div>

          {/* Mobile hamburger */}
          <button onClick={() => setMenuOpen(!menuOpen)} className="md:hidden p-2 rounded hover:bg-blue-700">
            <div className={`w-5 h-0.5 bg-white mb-1 transition-all ${menuOpen ? 'rotate-45 translate-y-1.5' : ''}`} />
            <div className={`w-5 h-0.5 bg-white mb-1 transition-all ${menuOpen ? 'opacity-0' : ''}`} />
            <div className={`w-5 h-0.5 bg-white transition-all ${menuOpen ? '-rotate-45 -translate-y-1.5' : ''}`} />
          </button>
        </div>

        {/* Mobile menu */}
        {menuOpen && (
          <div className="md:hidden pt-3 pb-2 border-t border-blue-500 mt-3 space-y-1">
            {links.map(l => (
              <Link key={l.to} to={l.to} onClick={() => setMenuOpen(false)}
                className="block px-2 py-2 rounded hover:bg-blue-700 text-sm">{l.label}</Link>
            ))}
            {user?.is_admin && (
              <Link to="/admin" onClick={() => setMenuOpen(false)}
                className="block px-2 py-2 rounded bg-red-700 text-sm font-medium">Admin</Link>
            )}
            <button onClick={handleLogout} className="w-full text-left px-2 py-2 rounded bg-red-600 text-sm mt-1">Logout</button>
          </div>
        )}
      </div>
    </nav>
  );
}

