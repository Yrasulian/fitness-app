import React, { useState, useEffect } from 'react';
import axios from 'axios';

const API = process.env.REACT_APP_API_URL || 'http://localhost:8000/api';

interface Plan { id: number; name: string; }
interface PlanExercise { id: number; exercise_name: string; muscle_group: string; target_sets: number; target_reps: string; target_weight: number|null; weight_unit: string; target_rir: number|null; notes?: string; }
interface Workout { id: number; session_name: string; workout_date: string; completed: boolean; duration_minutes: number; energy_level: number; exercise_logs: ExLog[]; }
interface ExLog { id: number; exercise_name: string; set_number: number; reps: number; weight: number; weight_unit: string; rir: number; }

export default function WorkoutTrackerPage() {
  const [plans, setPlans] = useState<Plan[]>([]);
  const [selectedPlanId, setSelectedPlanId] = useState('');
  const [planExercises, setPlanExercises] = useState<PlanExercise[]>([]);
  const [history, setHistory] = useState<Workout[]>([]);
  const [activeWorkout, setActiveWorkout] = useState<Workout | null>(null);
  const [sessionName, setSessionName] = useState('');
  const [activeExercise, setActiveExercise] = useState<PlanExercise | null>(null);
  const [exForm, setExForm] = useState({ exercise_name:'', set_number:1, reps:'', weight:'', weight_unit:'kg', rir:'' });
  const [endForm, setEndForm] = useState({ duration_minutes:'', energy_level:'7', notes:'' });
  const [tab, setTab] = useState<'active'|'history'>('active');

  useEffect(() => {
    axios.get(`${API}/training-plans`).then(r => setPlans(r.data));
    fetchHistory();
  }, []);

  useEffect(() => {
    if (!selectedPlanId) { setPlanExercises([]); return; }
    axios.get(`${API}/training-plans/${selectedPlanId}/exercises`)
      .then(r => setPlanExercises(r.data))
      .catch(() => setPlanExercises([]));
  }, [selectedPlanId]);

  const fetchHistory = async () => {
    const res = await axios.get(`${API}/workouts/history`);
    setHistory(res.data.data || []);
  };

  const startWorkout = async (e: React.FormEvent) => {
    e.preventDefault();
    const res = await axios.post(`${API}/workouts/start`, { session_name: sessionName, training_plan_id: selectedPlanId || null });
    setActiveWorkout(res.data);
    setSessionName('');
  };

  const logSet = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!activeWorkout) return;
    await axios.post(`${API}/workouts/${activeWorkout.id}/exercise`, exForm);
    const res = await axios.get(`${API}/workouts/history`);
    const updated = (res.data.data || []).find((w: Workout) => w.id === activeWorkout.id);
    if (updated) setActiveWorkout(updated);
    setHistory(res.data.data || []);
    setExForm(f => ({ ...f, reps:'', rir:'', set_number: f.set_number + 1 }));
  };

  const selectPlanEx = (ex: PlanExercise) => {
    setActiveExercise(ex);
    setExForm({ exercise_name:ex.exercise_name, set_number:1, reps:'', weight: ex.target_weight ? String(ex.target_weight) : '', weight_unit: ex.weight_unit || 'kg', rir: ex.target_rir !== null ? String(ex.target_rir) : '' });
  };

  const endWorkout = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!activeWorkout) return;
    await axios.post(`${API}/workouts/${activeWorkout.id}/end`, endForm);
    setActiveWorkout(null); setActiveExercise(null);
    fetchHistory(); setTab('history');
  };

  return (
    <div className="container mx-auto p-6">
      <h1 className="text-3xl font-bold mb-6">Workout Tracker</h1>
      <div className="flex gap-2 mb-6">
        <button onClick={() => setTab('active')} className={`px-4 py-2 rounded-lg font-medium ${tab==='active'?'bg-blue-600 text-white':'bg-gray-200'}`}>Active Workout</button>
        <button onClick={() => setTab('history')} className={`px-4 py-2 rounded-lg font-medium ${tab==='history'?'bg-blue-600 text-white':'bg-gray-200'}`}>History</button>
      </div>

      {tab === 'active' && (
        <div>
          {!activeWorkout ? (
            <form onSubmit={startWorkout} className="bg-white rounded-lg shadow p-6 max-w-md space-y-4">
              <h2 className="text-xl font-semibold">Start New Workout</h2>
              <div>
                <label className="text-sm text-gray-500">Training Plan (optional)</label>
                <select value={selectedPlanId} onChange={e => setSelectedPlanId(e.target.value)} className="w-full px-3 py-2 border rounded-lg mt-1">
                  <option value="">-- Free workout --</option>
                  {plans.map(p => <option key={p.id} value={p.id}>{p.name}</option>)}
                </select>
              </div>
              <input placeholder="Session name (e.g. Push Day)" value={sessionName} onChange={e => setSessionName(e.target.value)} className="w-full px-4 py-2 border rounded-lg" required />
              <button type="submit" className="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700">Start Workout</button>
            </form>
          ) : (
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-4">

              {/* Plan exercises sidebar */}
              {planExercises.length > 0 && (
                <div className="bg-white rounded-lg shadow p-4">
                  <h3 className="font-semibold mb-3 text-gray-600">Plan Exercises</h3>
                  <div className="space-y-1">
                    {planExercises.map(ex => (
                      <button key={ex.id} onClick={() => selectPlanEx(ex)}
                        className={`w-full text-left px-3 py-2 rounded-lg text-sm transition-colors ${activeExercise?.id===ex.id?'bg-blue-600 text-white':'hover:bg-gray-50 border'}`}>
                        <p className="font-medium">{ex.exercise_name}</p>
                        <p className={`text-xs ${activeExercise?.id===ex.id?'text-blue-100':'text-gray-400'}`}>{ex.target_sets}&times;{ex.target_reps} {ex.target_weight ? `@ ${ex.target_weight}${ex.weight_unit}` : ''} {ex.target_rir!==null?`RIR${ex.target_rir}`:''}</p>
                        {ex.notes && <p className={`text-xs mt-0.5 ${activeExercise?.id===ex.id?'text-blue-200':'text-yellow-600'}`}>{ex.notes}</p>}
                      </button>
                    ))}
                  </div>
                </div>
              )}

              {/* Main workout area */}
              <div className={`space-y-4 ${planExercises.length > 0 ? 'lg:col-span-2' : 'lg:col-span-3'}`}>
                <div className="bg-blue-50 border border-blue-200 rounded-lg p-4 flex justify-between items-center">
                  <div>
                    <h2 className="text-xl font-bold">{activeWorkout.session_name}</h2>
                    <p className="text-gray-500 text-sm">{activeWorkout.workout_date}</p>
                  </div>
                  <span className="bg-green-500 text-white px-3 py-1 rounded-full text-sm">In Progress</span>
                </div>

                <form onSubmit={logSet} className="bg-white rounded-lg shadow p-5">
                  <h3 className="font-semibold mb-3">Log Set {activeExercise && <span className="text-blue-600">&mdash; {activeExercise.exercise_name}</span>}</h3>
                  <div className="grid grid-cols-2 gap-3 mb-3">
                    <input placeholder="Exercise name" value={exForm.exercise_name} onChange={e => setExForm({...exForm, exercise_name:e.target.value})}
                      className="col-span-2 px-3 py-2 border rounded-lg" required />
                    <input type="number" placeholder="Set #" value={exForm.set_number} onChange={e => setExForm({...exForm, set_number:+e.target.value})} className="px-3 py-2 border rounded-lg" min={1} required />
                    <input type="number" placeholder="Reps" value={exForm.reps} onChange={e => setExForm({...exForm, reps:e.target.value})} className="px-3 py-2 border rounded-lg" />
                    <input type="number" placeholder="Weight" value={exForm.weight} onChange={e => setExForm({...exForm, weight:e.target.value})} className="px-3 py-2 border rounded-lg" step="0.5" />
                    <select value={exForm.weight_unit} onChange={e => setExForm({...exForm, weight_unit:e.target.value})} className="px-3 py-2 border rounded-lg">
                      <option>kg</option><option>lbs</option>
                    </select>
                    <input type="number" placeholder="RIR (0-5)" value={exForm.rir} onChange={e => setExForm({...exForm, rir:e.target.value})} className="px-3 py-2 border rounded-lg" min={0} max={5} />
                  </div>
                  <button type="submit" className="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">+ Log Set</button>
                </form>

                {activeWorkout.exercise_logs?.length > 0 && (
                  <div className="bg-white rounded-lg shadow p-5">
                    <h3 className="font-semibold mb-3">Geloggte S&auml;tze ({activeWorkout.exercise_logs.length})</h3>
                    <table className="w-full text-sm">
                      <thead><tr className="text-left text-gray-400 border-b"><th className="pb-2">Übung</th><th>Satz</th><th>Wdh.</th><th>Gewicht</th><th>RIR</th></tr></thead>
                      <tbody>{activeWorkout.exercise_logs.map(l => (
                        <tr key={l.id} className="border-b last:border-0">
                          <td className="py-2">{l.exercise_name}</td><td>{l.set_number}</td>
                          <td>{l.reps}</td><td>{l.weight} {l.weight_unit}</td><td>{l.rir ?? '-'}</td>
                        </tr>
                      ))}</tbody>
                    </table>
                  </div>
                )}

                <form onSubmit={endWorkout} className="bg-white rounded-lg shadow p-5">
                  <h3 className="font-semibold mb-3">Finish Workout</h3>
                  <div className="grid grid-cols-2 gap-3 mb-3">
                    <input type="number" placeholder="Duration (min)" value={endForm.duration_minutes} onChange={e => setEndForm({...endForm, duration_minutes:e.target.value})} className="px-3 py-2 border rounded-lg" required />
                    <select value={endForm.energy_level} onChange={e => setEndForm({...endForm, energy_level:e.target.value})} className="px-3 py-2 border rounded-lg">
                      {[1,2,3,4,5,6,7,8,9,10].map(n => <option key={n} value={n}>Energy: {n}/10</option>)}
                    </select>
                    <textarea placeholder="Notes" value={endForm.notes} onChange={e => setEndForm({...endForm, notes:e.target.value})} className="col-span-2 px-3 py-2 border rounded-lg" rows={2} />
                  </div>
                  <button type="submit" className="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700">&#10003; Finish Workout</button>
                </form>
              </div>
            </div>
          )}
        </div>
      )}

      {tab === 'history' && (
        <div className="space-y-3">
          {history.length === 0 ? <p className="text-center text-gray-500 py-12">No workouts yet.</p> :
            history.map(w => (
              <div key={w.id} className="bg-white rounded-lg shadow p-5 flex justify-between items-center">
                <div>
                  <h3 className="font-semibold">{w.session_name}</h3>
                  <p className="text-sm text-gray-500">{w.workout_date} &middot; {w.exercise_logs?.length ?? 0} sets</p>
                </div>
                <div className="text-sm text-gray-500 text-right">
                  {w.duration_minutes && <p>{w.duration_minutes} min</p>}
                  {w.energy_level && <p>Energy: {w.energy_level}/10</p>}
                </div>
              </div>
            ))}
        </div>
      )}
    </div>
  );
}
