import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { useAuth } from '../context/AuthContext';
import { useNavigate } from 'react-router-dom';

const API = process.env.REACT_APP_API_URL || 'http://localhost:8000/api';
const MUSCLES = ['Chest','Back','Shoulders','Biceps','Triceps','Legs','Glutes','Calves','Core','Cardio','Full Body'];

interface AdminUser { id: number; name: string; email: string; is_admin: boolean; created_at: string; }
interface CustomExercise { id: number; name: string; muscle_group: string; equipment: string; instructions: string; }
interface PlanExercise { id: number; exercise_name: string; target_sets: number; target_reps: string; target_weight: number|null; weight_unit: string; target_rir: number|null; }
interface TrainingPlan { id: number; name: string; template_type: string; duration_weeks: number; status: string; exercises: PlanExercise[]; }

export default function AdminDashboardPage() {
  const { user } = useAuth();
  const navigate = useNavigate();
  const [tab, setTab] = useState<'users'|'exercises'>('users');
  const [users, setUsers] = useState<AdminUser[]>([]);
  const [exercises, setExercises] = useState<CustomExercise[]>([]);
  const [exForm, setExForm] = useState({ name:'', muscle_group:'', equipment:'Barbell', instructions:'' });
  const [loading, setLoading] = useState(false);
  const [msg, setMsg] = useState('');
  const [editEx, setEditEx] = useState<CustomExercise|null>(null);
  const [selectedUser, setSelectedUser] = useState<AdminUser|null>(null);
  const [userPlans, setUserPlans] = useState<TrainingPlan[]>([]);
  const [plansLoading, setPlansLoading] = useState(false);
  const [showNewPlanForm, setShowNewPlanForm] = useState(false);
  const [newPlanForm, setNewPlanForm] = useState({ name:'', description:'', template_type:'Custom', duration_weeks:4 });
  const [addExPlanId, setAddExPlanId] = useState<number|null>(null);
  const [newExForm, setNewExForm] = useState({ exercise_name:'', muscle_group:'', target_sets:3, target_reps:'10', target_weight:'', weight_unit:'kg', target_rir:'', rest_seconds:'', notes:'' });

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

  const saveExercise = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!editEx) return;
    await axios.put(`${API}/admin/exercises/${editEx.id}`, editEx);
    setEditEx(null);
    fetchExercises();
  };

  const viewUserPlans = async (u: AdminUser) => {
    setSelectedUser(u);
    setShowNewPlanForm(false);
    setAddExPlanId(null);
    setPlansLoading(true);
    try {
      const r = await axios.get(`${API}/admin/users/${u.id}/training-plans`);
      setUserPlans(r.data);
    } catch { setUserPlans([]); }
    finally { setPlansLoading(false); }
  };

  const createPlanForUser = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedUser) return;
    const r = await axios.post(`${API}/admin/users/${selectedUser.id}/training-plans`, newPlanForm);
    setUserPlans(prev => [r.data, ...prev]);
    setShowNewPlanForm(false);
    setNewPlanForm({ name:'', description:'', template_type:'Custom', duration_weeks:4 });
  };

  const addExerciseToPlan = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!addExPlanId) return;
    const r = await axios.post(`${API}/admin/training-plans/${addExPlanId}/exercises`, newExForm);
    setUserPlans(prev => prev.map(p => p.id === addExPlanId
      ? { ...p, exercises: [...p.exercises, r.data] }
      : p
    ));
    setAddExPlanId(null);
    setNewExForm({ exercise_name:'', muscle_group:'', target_sets:3, target_reps:'10', target_weight:'', weight_unit:'kg', target_rir:'', rest_seconds:'', notes:'' });
  };

  const removeExerciseFromPlan = async (planId: number, exId: number) => {
    await axios.delete(`${API}/admin/training-plans/${planId}/exercises/${exId}`);
    setUserPlans(prev => prev.map(p => p.id === planId
      ? { ...p, exercises: p.exercises.filter(ex => ex.id !== exId) }
      : p
    ));
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
                  <td className="px-4 py-3">
                    <button onClick={() => viewUserPlans(u)} className="text-green-600 hover:underline text-xs">Plans</button>
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
                  <div key={ex.id} className="bg-white rounded-lg shadow p-4">
                    {editEx?.id === ex.id ? (
                      <form onSubmit={saveExercise} className="space-y-2">
                        <input value={editEx.name} onChange={e => setEditEx({...editEx, name:e.target.value})}
                          className="w-full px-3 py-1.5 border rounded text-sm" required />
                        <div className="grid grid-cols-2 gap-2">
                          <select value={editEx.muscle_group} onChange={e => setEditEx({...editEx, muscle_group:e.target.value})}
                            className="px-3 py-1.5 border rounded text-sm">
                            {MUSCLES.map(m => <option key={m}>{m}</option>)}
                          </select>
                          <input value={editEx.equipment} onChange={e => setEditEx({...editEx, equipment:e.target.value})}
                            placeholder="Equipment" className="px-3 py-1.5 border rounded text-sm" />
                        </div>
                        <textarea value={editEx.instructions} onChange={e => setEditEx({...editEx, instructions:e.target.value})}
                          placeholder="Instructions" className="w-full px-3 py-1.5 border rounded text-sm" rows={2} />
                        <div className="flex gap-2">
                          <button type="submit" className="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">Save</button>
                          <button type="button" onClick={() => setEditEx(null)} className="bg-gray-200 px-3 py-1 rounded text-sm">Cancel</button>
                        </div>
                      </form>
                    ) : (
                      <div className="flex justify-between items-center">
                        <div>
                          <p className="font-semibold">{ex.name}</p>
                          <p className="text-sm text-gray-500">{ex.muscle_group} &middot; {ex.equipment}</p>
                          {ex.instructions && <p className="text-xs text-gray-400 mt-1">{ex.instructions}</p>}
                        </div>
                        <div className="flex gap-3 ml-4">
                          <button onClick={() => setEditEx({...ex})} className="text-blue-500 hover:text-blue-700 text-sm">Edit</button>
                          <button onClick={() => deleteExercise(ex.id)} className="text-red-400 hover:text-red-600 text-sm">&times;</button>
                        </div>
                      </div>
                    )}
                  </div>
                ))}
              </div>
            )}
          </div>
        </div>
      )}

      {/* Training Plans Modal */}
      {selectedUser && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[85vh] flex flex-col">

            {/* Header */}
            <div className="flex justify-between items-center p-5 border-b">
              <div>
                <h2 className="text-xl font-bold">{selectedUser.name}</h2>
                <p className="text-sm text-gray-500">{selectedUser.email}</p>
              </div>
              <div className="flex items-center gap-3">
                <button onClick={() => { setShowNewPlanForm(!showNewPlanForm); setAddExPlanId(null); }}
                  className="bg-blue-600 text-white text-sm px-3 py-1.5 rounded-lg hover:bg-blue-700">
                  + New Plan
                </button>
                <button onClick={() => setSelectedUser(null)} className="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
              </div>
            </div>

            {/* New Plan Form */}
            {showNewPlanForm && (
              <form onSubmit={createPlanForUser} className="border-b p-4 bg-blue-50 space-y-2">
                <h3 className="font-semibold text-sm text-blue-800">Create Plan for {selectedUser.name}</h3>
                <input placeholder="Plan name *" value={newPlanForm.name} onChange={e => setNewPlanForm({...newPlanForm, name:e.target.value})}
                  className="w-full px-3 py-2 border rounded-lg text-sm" required />
                <input placeholder="Description" value={newPlanForm.description} onChange={e => setNewPlanForm({...newPlanForm, description:e.target.value})}
                  className="w-full px-3 py-2 border rounded-lg text-sm" />
                <div className="flex gap-2">
                  <select value={newPlanForm.template_type} onChange={e => setNewPlanForm({...newPlanForm, template_type:e.target.value})}
                    className="flex-1 px-3 py-2 border rounded-lg text-sm">
                    {['PPL','Upper/Lower','Full Body','Push/Pull/Legs','Custom'].map(t => <option key={t}>{t}</option>)}
                  </select>
                  <input type="number" placeholder="Weeks" min={1} value={newPlanForm.duration_weeks}
                    onChange={e => setNewPlanForm({...newPlanForm, duration_weeks:+e.target.value})}
                    className="w-24 px-3 py-2 border rounded-lg text-sm" />
                </div>
                <div className="flex gap-2">
                  <button type="submit" className="bg-blue-600 text-white px-4 py-1.5 rounded-lg text-sm hover:bg-blue-700">Create</button>
                  <button type="button" onClick={() => setShowNewPlanForm(false)} className="bg-gray-200 px-4 py-1.5 rounded-lg text-sm">Cancel</button>
                </div>
              </form>
            )}

            {/* Plans List */}
            <div className="overflow-y-auto p-5 space-y-4">
              {plansLoading && <p className="text-center text-gray-400">Loading...</p>}
              {!plansLoading && userPlans.length === 0 && (
                <p className="text-center text-gray-400 py-8">No training plans yet. Create one above!</p>
              )}
              {userPlans.map(plan => (
                <div key={plan.id} className="border rounded-lg overflow-hidden">
                  <div className="bg-gray-50 px-4 py-3 flex justify-between items-center">
                    <div>
                      <span className="font-semibold">{plan.name}</span>
                      <span className="text-gray-400 text-sm ml-2">{plan.template_type} &middot; {plan.duration_weeks} weeks</span>
                    </div>
                    <div className="flex items-center gap-2">
                      <span className={`text-xs px-2 py-1 rounded-full ${plan.status === 'Active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'}`}>{plan.status}</span>
                      <button onClick={() => setAddExPlanId(addExPlanId === plan.id ? null : plan.id)}
                        className="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded hover:bg-blue-200">
                        + Exercise
                      </button>
                    </div>
                  </div>

                  {/* Add Exercise Form */}
                  {addExPlanId === plan.id && (
                    <form onSubmit={addExerciseToPlan} className="bg-blue-50 border-b p-3 space-y-2">
                      <input placeholder="Exercise name *" value={newExForm.exercise_name}
                        onChange={e => setNewExForm({...newExForm, exercise_name:e.target.value})}
                        className="w-full px-3 py-1.5 border rounded text-sm" required />
                      <div className="grid grid-cols-3 gap-2">
                        <div>
                          <label className="text-xs text-gray-500">Muscle</label>
                          <select value={newExForm.muscle_group} onChange={e => setNewExForm({...newExForm, muscle_group:e.target.value})}
                            className="w-full px-2 py-1.5 border rounded text-sm">
                            <option value="">-</option>
                            {MUSCLES.map(m => <option key={m}>{m}</option>)}
                          </select>
                        </div>
                        <div>
                          <label className="text-xs text-gray-500">Sets</label>
                          <input type="number" min={1} value={newExForm.target_sets}
                            onChange={e => setNewExForm({...newExForm, target_sets:+e.target.value})}
                            className="w-full px-2 py-1.5 border rounded text-sm" />
                        </div>
                        <div>
                          <label className="text-xs text-gray-500">Reps</label>
                          <input placeholder="8-12" value={newExForm.target_reps}
                            onChange={e => setNewExForm({...newExForm, target_reps:e.target.value})}
                            className="w-full px-2 py-1.5 border rounded text-sm" />
                        </div>
                        <div>
                          <label className="text-xs text-gray-500">Weight</label>
                          <input type="number" step="0.5" placeholder="opt." value={newExForm.target_weight}
                            onChange={e => setNewExForm({...newExForm, target_weight:e.target.value})}
                            className="w-full px-2 py-1.5 border rounded text-sm" />
                        </div>
                        <div>
                          <label className="text-xs text-gray-500">Unit</label>
                          <select value={newExForm.weight_unit} onChange={e => setNewExForm({...newExForm, weight_unit:e.target.value})}
                            className="w-full px-2 py-1.5 border rounded text-sm">
                            <option>kg</option><option>lbs</option>
                          </select>
                        </div>
                        <div>
                          <label className="text-xs text-gray-500">RIR</label>
                          <input type="number" min={0} max={5} placeholder="0-5" value={newExForm.target_rir}
                            onChange={e => setNewExForm({...newExForm, target_rir:e.target.value})}
                            className="w-full px-2 py-1.5 border rounded text-sm" />
                        </div>
                      </div>
                      <div className="flex gap-2">
                        <button type="submit" className="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">Add</button>
                        <button type="button" onClick={() => setAddExPlanId(null)} className="bg-gray-200 px-3 py-1 rounded text-sm">Cancel</button>
                      </div>
                    </form>
                  )}

                  {/* Exercises Table */}
                  {plan.exercises.length === 0 ? (
                    <p className="text-sm text-gray-400 px-4 py-2 italic">No exercises yet</p>
                  ) : (
                    <table className="w-full text-sm">
                      <thead className="bg-gray-50 border-t border-b text-xs text-gray-500">
                        <tr>
                          <th className="text-left px-4 py-2">Exercise</th>
                          <th className="text-left px-4 py-2">Sets</th>
                          <th className="text-left px-4 py-2">Reps</th>
                          <th className="text-left px-4 py-2">Weight</th>
                          <th className="text-left px-4 py-2">RIR</th>
                          <th className="px-4 py-2"></th>
                        </tr>
                      </thead>
                      <tbody>
                        {plan.exercises.map(ex => (
                          <tr key={ex.id} className="border-b last:border-0 hover:bg-gray-50">
                            <td className="px-4 py-2 font-medium">{ex.exercise_name}</td>
                            <td className="px-4 py-2 text-gray-600">{ex.target_sets}</td>
                            <td className="px-4 py-2 text-gray-600">{ex.target_reps}</td>
                            <td className="px-4 py-2 text-gray-600">{ex.target_weight ? `${ex.target_weight} ${ex.weight_unit}` : '-'}</td>
                            <td className="px-4 py-2 text-gray-600">{ex.target_rir ?? '-'}</td>
                            <td className="px-4 py-2">
                              <button onClick={() => removeExerciseFromPlan(plan.id, ex.id)}
                                className="text-red-400 hover:text-red-600 text-xs">&times;</button>
                            </td>
                          </tr>
                        ))}
                      </tbody>
                    </table>
                  )}
                </div>
              ))}
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
