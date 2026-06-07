import React from 'react';
import { useAuth } from '../context/AuthContext';

export default function ProfilePage() {
  const { user, logout } = useAuth();

  return (
    <div className="container mx-auto p-6 max-w-lg">
      <h1 className="text-3xl font-bold mb-6">Profile</h1>
      <div className="bg-white rounded-lg shadow p-6 space-y-4">
        <div className="flex items-center gap-4 pb-4 border-b">
          <div className="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center text-white text-2xl font-bold">
            {user?.name?.charAt(0).toUpperCase()}
          </div>
          <div>
            <h2 className="text-xl font-semibold">{user?.name}</h2>
            <p className="text-gray-500">{user?.email}</p>
          </div>
        </div>
        <div className="grid grid-cols-2 gap-4">
          {[['Age', user?.age ?? '—'],['Gender', user?.gender ?? '—'],['Goal', user?.goal ?? '—'],['Level', user?.experience_level ?? '—']].map(([label, val]) => (
            <div key={label}><p className="text-sm text-gray-500">{label}</p><p className="font-medium">{val}</p></div>
          ))}
        </div>
        <button onClick={logout} className="w-full mt-4 bg-red-600 text-white py-2 rounded-lg hover:bg-red-700">Logout</button>
      </div>
    </div>
  );
}
