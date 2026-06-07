import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { useAuth } from '../context/AuthContext';
import { useNavigate } from 'react-router-dom';

const API = process.env.REACT_APP_API_URL || 'http://localhost:8000/api';
const MUSCLES = ['Chest','Back','Shoulders','Biceps','Triceps','Legs','Glutes','Calves','Core','Cardio','Full Body'];

interface AdminUser { id: number; name: string; email: string; is_admin: boolean; created_at: string; }
interface CustomExercise { id: number; name: string; muscle_group: string; equipment: string; instructions: string; }

export default function AdminDashboardPage() {
  const { user } = useAuth();
  const navigate = useNavigate();
  const [tab, setTab] = useState<'users'|'exercises'>('users');
  const [users, setUsers] = useState<AdminUser[]>([]);
  const [exercises, setExercises] = useState<CustomExercise[]>([]);
  const [exForm, setExForm] = useState({ name:'', muscle_group:'', equipment:'Barbell', instructions:'' });
  const [loading, setLoading] = useState(false);
  const [msg, setMsg] = useState('');

  useEffect(() => {
    if (!user?.is_admin) { navigate('/dashboard'); return; }
    fetchUsers();
    fetchExercises();
  }, [user]);

  const fetchUsers = async () => {
    const r = await axios.get(`${API}/admin/users`);
    setUsers(r.data);
  };

  const fetchExercises = async () => {
    const r = await axios.get(`${API}/admin/exercises`);
    setExercises(r.data);
  };

  const toggleAdmin = async (id: number) => {
    await axios.put(`${API}/admin/users/${id}/toggle-admin`);
    fetchUsers();
  };

  const addExercise = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    try {
      await axios.post(`${API}/admin/exercises`, exForm);
      setMsg('Exercise added!');
      setExForm({ name:'', muscle_group:'', equipment:'Barbell', instructions:'' });
      fetchExercises();
    } catch (err: any) {
      setMsg(err?.response?.data?.message || 'Error');
    } finally { setLoading(false); }
  };

  const deleteExercise = async (id: number) => {
    if (!window.confirm('Delete this exercise?')) return;
    await axios.delete(`${API}/admin/exercises/${id}`);
    fetchExercises();
  };

  return (
    <div className="container mx-auto p-6">
      <div className="flex items-center gap-3 mb-6">
        <h1 className="text-3xl font-bold">Admin Dashboard</h1>
        <span className="bg-red-100 text-red-700 text-xs px-2 py-1 rounded-full font-medium">Admin</span>
      </div>

      <div className="flex gap-2 mb-6">
        <button onClick={() => setTab('users')} className={`px-4 py-2 rounded-lg font-medium ${tab==='users'?'bg-blue-600 text-white':'bg-gray-200'}`}>
          Users ({users.length})
        </button>
        <button onClick={() => setTab('exercises')} className={`px-4 py-2 rounded-lg font-medium ${tab==='exercises'?'bg-blue-600 text-white':'bg-gray-200'}`}>
          Custom Exercises ({exercises.length})
        </button>
      </div>

      {tab === 'users' && (
        <div className="bg-white rounded-lg shadow overflow-hidden">
          <table className="w-full text-sm">
            <thead className="bg-gray-50 border-b">
              <tr>
                <th className="text-left px-4 py-3 text-gray-600">ID</th>
                <th className="text-left px-4 py-3 text-gray-600">Name</th>
                <th className="text-left px-4 py-3 text-gray-600">Email</th>
                <th className="text-left px-4 py-3 text-gray-600">Registered</th>
                <th className="text-left px-4 py-3 text-gray-600">Role</th>
                <th className="px-4 py-3"></th>
              </tr>
            </thead>
            <tbody>
              {users.map(u => (
                <tr key={u.id} className="border-b hover:bg-gray-50">
                  <td className="px-4 py-3 text-gray-400">{u.id}</td>
                  <td className="px-4 py-3 font-medium">{u.name}</td>
                  <td className="px-4 py-3 text-gray-600">{u.email}</td>
                  <td className="px-4 py-3 text-gray-400">{new Date(u.created_at).toLocaleDateString()}</td>
                  <td className="px-4 py-3">
                    <span className={`px-2 py-1 rounded-full text-xs font-medium ${u.is_admin ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600'}`}>
                      {u.is_admin ? 'Admin' : 'User'}
                    </span>
                  </td>
                  <td className="px-4 py-3">
                    {u.id !== user?.id && (
                      <button onClick={() => toggleAdmin(u.id)} className="text-blue-600 hover:underline text-xs">
                        {u.is_admin ? 'Remove Admin' : 'Make Admin'}
                      </button>
                    )}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      {tab === 'exercises' && (
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <div>
            <h2 className="text-xl font-semibold mb-4">Add Custom Exercise</h2>
            {msg && <div className={`mb-3 px-4 py-2 rounded-lg text-sm ${msg.includes('Error') ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'}`}>{msg}</div>}
            <form onSubmit={addExercise} className="bg-white rounded-lg shadow p-5 space-y-3">
              <div>
                <label className="text-sm font-medium text-gray-600">Exercise Name</label>
                <input value={exForm.name} onChange={e => setExForm({...exForm, name:e.target.value})}
                  className="w-full px-3 py-2 border rounded-lg mt-1" placeholder="e.g. Cable Fly Crossover" required />
              </div>
              <div>
                <label className="text-sm font-medium text-gray-600">Muscle Group</label>
                <select value={exForm.muscle_group} onChange={e => setExForm({...exForm, muscle_group:e.target.value})}
                  className="w-full px-3 py-2 border rounded-lg mt-1" required>
                  <option value="">-- Select --</option>
                  {MUSCLES.map(m => <option key={m}>{m}</option>)}
                </select>
              </div>
              <div>
                <label className="text-sm font-medium text-gray-600">Equipment</label>
                <select value={exForm.equipment} onChange={e => setExForm({...exForm, equipment:e.target.value})}
                  className="w-full px-3 py-2 border rounded-lg mt-1">
                  {['Barbell','Dumbbell','Cable','Machine','Bodyweight','Kettlebell','EZ Bar','Resistance Band','Other'].map(eq => <option key={eq}>{eq}</option>)}
                </select>
              </div>
              <div>
                <label className="text-sm font-medium text-gray-600">Instructions (optional)</label>
                <textarea value={exForm.instructions} onChange={e => setExForm({...exForm, instructions:e.target.value})}
                  className="w-full px-3 py-2 border rounded-lg mt-1 text-sm" rows={3} placeholder="How to perform..." />
              </div>
              <button type="submit" disabled={loading}
                className="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 disabled:opacity-50">
                {loading ? 'Adding...' : '+ Add Exercise'}
              </button>
            </form>
          </div>

          <div>
            <h2 className="text-xl font-semibold mb-4">Custom Exercise Library ({exercises.length})</h2>
            {exercises.length === 0 ? (
              <p className="text-gray-400 text-center py-8 bg-white rounded-lg shadow">No custom exercises yet.</p>
            ) : (
              <div className="space-y-2 max-h-[600px] overflow-y-auto">
                {exercises.map(ex => (
                  <div key={ex.id} className="bg-white rounded-lg shadow p-4 flex justify-between items-center">
                    <div>
                      <p className="font-semibold">{ex.name}</p>
                      <p className="text-sm text-gray-500">{ex.muscle_group} · {ex.equipment}</p>
                      {ex.instructions && <p className="text-xs text-gray-400 mt-1">{ex.instructions}</p>}
                    </div>
                    <button onClick={() => deleteExercise(ex.id)} className="text-red-400 hover:text-red-600 ml-4">✕</button>
                  </div>
                ))}
              </div>
            )}
          </div>
        </div>
      )}
    </div>
  );
}
