import React, { useState, useEffect, useCallback } from 'react';
import axios from 'axios';

const API = process.env.REACT_APP_API_URL || 'http://localhost:8000/api';

interface Plan { id: number; name: string; description: string; template_type: string; duration_weeks: number; status: string; }
interface PlanExercise { id: number; exercise_name: string; muscle_group: string; target_sets: number; target_reps: string; target_weight: number|null; weight_unit: string; target_rir: number|null; rest_seconds: number|null; notes: string; order_index: number; }
interface Exercise { name: string; muscle_group: string; equipment: string; }

const MUSCLES = ['Chest','Back','Shoulders','Biceps','Triceps','Legs','Glutes','Calves','Core','Cardio','Full Body'];

export default function TrainingPlansPage() {
  const [plans, setPlans] = useState<Plan[]>([]);
  const [selectedPlan, setSelectedPlan] = useState<Plan | null>(null);
  const [planExercises, setPlanExercises] = useState<PlanExercise[]>([]);
  const [showPlanForm, setShowPlanForm] = useState(false);
  const [showExForm, setShowExForm] = useState(false);
  const [planForm, setPlanForm] = useState({ name:'', description:'', template_type:'Custom', duration_weeks:4 });
  const [exForm, setExForm] = useState({ exercise_name:'', muscle_group:'', target_sets:3, target_reps:'10', target_weight:'', weight_unit:'kg', target_rir:'', rest_seconds:'', notes:'' });
  const [search, setSearch] = useState('');
  const [muscleFilter, setMuscleFilter] = useState('');
  const [exercises, setExercises] = useState<Exercise[]>([]);
  const [showSearch, setShowSearch] = useState(false);
  const [setupDone, setSetupDone] = useState(false);

  useEffect(() => { fetchPlans(); ensureSetup(); }, []);

  const ensureSetup = async () => {
    try { await axios.post(`${API}/exercises/setup`); setSetupDone(true); } catch { setSetupDone(true); }
  };

  const fetchPlans = async () => {
    const res = await axios.get(`${API}/training-plans`);
    setPlans(res.data);
  };

  const fetchPlanExercises = useCallback(async (plan: Plan) => {
    try {
      const res = await axios.get(`${API}/training-plans/${plan.id}/exercises`);
      setPlanExercises(res.data);
    } catch { setPlanExercises([]); }
  }, []);

  const searchExercises = async (q: string, muscle: string) => {
    const res = await axios.get(`${API}/exercises`, { params: { search: q, muscle } });
    setExercises(res.data);
  };

  useEffect(() => {
    if (showSearch) searchExercises(search, muscleFilter);
  }, [search, muscleFilter, showSearch]);

  const selectPlan = (plan: Plan) => { setSelectedPlan(plan); fetchPlanExercises(plan); };

  const createPlan = async (e: React.FormEvent) => {
    e.preventDefault();
    await axios.post(`${API}/training-plans`, planForm);
    setShowPlanForm(false);
    setPlanForm({ name:'', description:'', template_type:'Custom', duration_weeks:4 });
    fetchPlans();
  };

  const deletePlan = async (id: number) => {
    if (!window.confirm('Delete this plan?')) return;
    await axios.delete(`${API}/training-plans/${id}`);
    if (selectedPlan?.id === id) setSelectedPlan(null);
    fetchPlans();
  };

  const pickExercise = (ex: Exercise) => {
    setExForm(f => ({ ...f, exercise_name: ex.name, muscle_group: ex.muscle_group }));
    setShowSearch(false);
  };

  const addExercise = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedPlan) return;
    await axios.post(`${API}/training-plans/${selectedPlan.id}/exercises`, exForm);
    setShowExForm(false);
    setExForm({ exercise_name:'', muscle_group:'', target_sets:3, target_reps:'10', target_weight:'', weight_unit:'kg', target_rir:'', rest_seconds:'', notes:'' });
    fetchPlanExercises(selectedPlan);
  };

  const removeExercise = async (exId: number) => {
    if (!selectedPlan) return;
    await axios.delete(`${API}/training-plans/${selectedPlan.id}/exercises/${exId}`);
    fetchPlanExercises(selectedPlan);
  };

  return (
    <div className="container mx-auto p-6">
      {!selectedPlan ? (
        <>
          <div className="flex justify-between items-center mb-6">
            <h1 className="text-3xl font-bold">Training Plans</h1>
            <button onClick={() => setShowPlanForm(!showPlanForm)} className="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">+ New Plan</button>
          </div>

          {showPlanForm && (
            <form onSubmit={createPlan} className="bg-white rounded-lg shadow p-6 mb-6 space-y-3">
              <h2 className="text-xl font-semibold">Create Plan</h2>
              <input placeholder="Plan name" value={planForm.name} onChange={e => setPlanForm({...planForm, name:e.target.value})} className="w-full px-4 py-2 border rounded-lg" required />
              <textarea placeholder="Description" value={planForm.description} onChange={e => setPlanForm({...planForm, description:e.target.value})} className="w-full px-4 py-2 border rounded-lg" rows={2} />
              <div className="grid grid-cols-2 gap-3">
                <select value={planForm.template_type} onChange={e => setPlanForm({...planForm, template_type:e.target.value})} className="px-4 py-2 border rounded-lg">
                  {['PPL','UL','Full Body','Push/Pull/Legs','Custom'].map(t => <option key={t}>{t}</option>)}
                </select>
                <input type="number" placeholder="Weeks" value={planForm.duration_weeks} min={1} onChange={e => setPlanForm({...planForm, duration_weeks:+e.target.value})} className="px-4 py-2 border rounded-lg" />
              </div>
              <div className="flex gap-2">
                <button type="submit" className="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Save</button>
                <button type="button" onClick={() => setShowPlanForm(false)} className="bg-gray-200 px-4 py-2 rounded-lg">Cancel</button>
              </div>
            </form>
          )}

          {plans.length === 0 ? (
            <p className="text-center text-gray-500 py-12">No plans yet.</p>
          ) : (
            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
              {plans.map(plan => (
                <div key={plan.id} className="bg-white rounded-lg shadow p-5 cursor-pointer hover:shadow-md transition-shadow" onClick={() => selectPlan(plan)}>
                  <div className="flex justify-between items-start mb-2">
                    <h3 className="text-lg font-semibold">{plan.name}</h3>
                    <span className={`text-xs px-2 py-1 rounded-full ${plan.status === 'Active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'}`}>{plan.status}</span>
                  </div>
                  {plan.description && <p className="text-gray-500 text-sm mb-2">{plan.description}</p>}
                  <p className="text-sm text-gray-400">{plan.template_type} &middot; {plan.duration_weeks} weeks</p>
                  <div className="flex justify-between items-center mt-3">
                    <span className="text-blue-600 text-sm font-medium">Open Plan &rarr;</span>
                    <button onClick={e => { e.stopPropagation(); deletePlan(plan.id); }} className="text-red-400 text-sm hover:text-red-600">Delete</button>
                  </div>
                </div>
              ))}
            </div>
          )}
        </>
      ) : (
        <>
          <div className="flex items-center gap-3 mb-6">
            <button onClick={() => setSelectedPlan(null)} className="text-gray-500 hover:text-gray-700">&larr; Back</button>
            <h1 className="text-2xl font-bold">{selectedPlan.name}</h1>
            <span className="text-gray-400">|</span>
            <span className="text-gray-500 text-sm">{selectedPlan.template_type} &middot; {selectedPlan.duration_weeks} weeks</span>
          </div>

          <div className="flex justify-between items-center mb-4">
            <h2 className="text-xl font-semibold">Exercises ({planExercises.length})</h2>
            <button onClick={() => { setShowExForm(!showExForm); setShowSearch(false); }} className="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">+ Add Exercise</button>
          </div>

          {showExForm && (
            <div className="bg-white rounded-lg shadow p-5 mb-4">
              <div className="flex gap-2 mb-3">
                <input placeholder="Search exercise..." value={exForm.exercise_name}
                  onChange={e => { setExForm({...exForm, exercise_name:e.target.value}); setSearch(e.target.value); setShowSearch(true); }}
                  onFocus={() => { setShowSearch(true); searchExercises(search, muscleFilter); }}
                  className="flex-1 px-3 py-2 border rounded-lg" />
                <select value={muscleFilter} onChange={e => { setMuscleFilter(e.target.value); setShowSearch(true); }} className="px-3 py-2 border rounded-lg">
                  <option value="">All muscles</option>
                  {MUSCLES.map(m => <option key={m}>{m}</option>)}
                </select>
              </div>

              {showSearch && exercises.length > 0 && (
                <div className="border rounded-lg max-h-48 overflow-y-auto mb-3">
                  {exercises.slice(0, 20).map((ex, i) => (
                    <button key={i} type="button" onClick={() => pickExercise(ex)}
                      className="w-full text-left px-3 py-2 hover:bg-blue-50 border-b last:border-0 flex justify-between">
                      <span className="font-medium">{ex.name}</span>
                      <span className="text-gray-400 text-sm">{ex.muscle_group} &middot; {ex.equipment}</span>
                    </button>
                  ))}
                </div>
              )}

              <form onSubmit={addExercise} className="space-y-3">
                {exForm.muscle_group && <p className="text-sm text-blue-600">Selected: <strong>{exForm.exercise_name}</strong> ({exForm.muscle_group})</p>}
                <div className="grid grid-cols-3 gap-3">
                  <div><label className="text-xs text-gray-500">Sets</label>
                    <input type="number" value={exForm.target_sets} min={1} onChange={e => setExForm({...exForm, target_sets:+e.target.value})} className="w-full px-3 py-2 border rounded-lg" required /></div>
                  <div><label className="text-xs text-gray-500">Reps</label>
                    <input placeholder="e.g. 8-12" value={exForm.target_reps} onChange={e => setExForm({...exForm, target_reps:e.target.value})} className="w-full px-3 py-2 border rounded-lg" required /></div>
                  <div><label className="text-xs text-gray-500">RIR</label>
                    <input type="number" placeholder="0-5" value={exForm.target_rir} min={0} max={5} onChange={e => setExForm({...exForm, target_rir:e.target.value})} className="w-full px-3 py-2 border rounded-lg" /></div>
                  <div><label className="text-xs text-gray-500">Target Weight</label>
                    <input type="number" placeholder="Optional" value={exForm.target_weight} step="0.5" onChange={e => setExForm({...exForm, target_weight:e.target.value})} className="w-full px-3 py-2 border rounded-lg" /></div>
                  <div><label className="text-xs text-gray-500">Unit</label>
                    <select value={exForm.weight_unit} onChange={e => setExForm({...exForm, weight_unit:e.target.value})} className="w-full px-3 py-2 border rounded-lg"><option>kg</option><option>lbs</option></select></div>
                  <div><label className="text-xs text-gray-500">Rest (sec)</label>
                    <input type="number" placeholder="90" value={exForm.rest_seconds} onChange={e => setExForm({...exForm, rest_seconds:e.target.value})} className="w-full px-3 py-2 border rounded-lg" /></div>
                </div>
                <textarea placeholder="Notes (optional)" value={exForm.notes} onChange={e => setExForm({...exForm, notes:e.target.value})} className="w-full px-3 py-2 border rounded-lg text-sm" rows={2} />
                <div className="flex gap-2">
                  <button type="submit" disabled={!exForm.exercise_name} className="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 disabled:opacity-50">Add to Plan</button>
                  <button type="button" onClick={() => setShowExForm(false)} className="bg-gray-200 px-4 py-2 rounded-lg">Cancel</button>
                </div>
              </form>
            </div>
          )}

          {planExercises.length === 0 ? (
            <p className="text-center text-gray-400 py-10">No exercises in this plan yet. Add some!</p>
          ) : (
            <div className="space-y-2">
              {planExercises.map((ex, i) => (
                <div key={ex.id} className="bg-white rounded-lg shadow p-4 flex justify-between items-center">
                  <div className="flex items-center gap-3">
                    <span className="text-gray-400 text-sm w-5">{i + 1}</span>
                    <div>
                      <p className="font-semibold">{ex.exercise_name}</p>
                      <p className="text-sm text-gray-500">{ex.muscle_group}</p>
                    </div>
                  </div>
                  <div className="flex items-center gap-4 text-sm text-gray-600">
                    <span className="bg-blue-50 px-2 py-1 rounded">{ex.target_sets} &times; {ex.target_reps}</span>
                    {ex.target_weight && <span>{ex.target_weight} {ex.weight_unit}</span>}
                    {ex.target_rir !== null && <span>RIR {ex.target_rir}</span>}
                    {ex.rest_seconds && <span>{ex.rest_seconds}s rest</span>}
                    <button onClick={() => removeExercise(ex.id)} className="text-red-400 hover:text-red-600 ml-2">&times;</button>
                  </div>
                </div>
              ))}
            </div>
          )}
        </>
      )}
    </div>
  );
}
