import React, { useState, useEffect } from 'react';
import axios from 'axios';

const API = process.env.REACT_APP_API_URL || 'http://localhost:8000/api';

interface Meal { id: number; meal_type: string; food_item: string; quantity: number; unit: string; calories: number; protein: number; carbs: number; fats: number; }

export default function NutritionPage() {
  const [date, setDate] = useState(new Date().toISOString().split('T')[0]);
  const [meals, setMeals] = useState<Meal[]>([]);
  const [totals, setTotals] = useState<any>({});
  const [showForm, setShowForm] = useState(false);
  const [form, setForm] = useState({ log_date: date, meal_type: 'Breakfast', food_item: '', quantity: '', unit: 'g', calories: '', protein: '', carbs: '', fats: '' });

  useEffect(() => { fetchNutrition(); }, [date]);

  const fetchNutrition = async () => {
    try {
      const res = await axios.get(`${API}/nutrition/${date}`);
      setMeals(res.data.meals || []);
      setTotals(res.data.totals || {});
    } catch { setMeals([]); setTotals({}); }
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    await axios.post(`${API}/nutrition/log`, { ...form, log_date: date });
    setShowForm(false);
    setForm({ log_date: date, meal_type: 'Breakfast', food_item: '', quantity: '', unit: 'g', calories: '', protein: '', carbs: '', fats: '' });
    fetchNutrition();
  };

  const deleteMeal = async (id: number) => {
    await axios.delete(`${API}/nutrition/${id}`);
    fetchNutrition();
  };

  const mealTypes = ['Breakfast', 'Lunch', 'Dinner', 'Snack', 'Post-Workout'];
  const groupedMeals = mealTypes.map(type => ({ type, meals: meals.filter(m => m.meal_type === type) })).filter(g => g.meals.length > 0);

  return (
    <div className="container mx-auto p-6">
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-3xl font-bold">Nutrition</h1>
        <div className="flex gap-3 items-center">
          <input type="date" value={date} onChange={e => setDate(e.target.value)} className="px-3 py-2 border rounded-lg" />
          <button onClick={() => setShowForm(!showForm)} className="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">+ Log Meal</button>
        </div>
      </div>

      <div className="grid grid-cols-4 gap-4 mb-6">
        {[['Calories', totals.calories ?? 0, 'kcal', 'bg-orange-50 border-orange-200'],
          ['Protein', totals.protein ?? 0, 'g', 'bg-red-50 border-red-200'],
          ['Carbs', totals.carbs ?? 0, 'g', 'bg-yellow-50 border-yellow-200'],
          ['Fats', totals.fats ?? 0, 'g', 'bg-blue-50 border-blue-200']].map(([label, val, unit, cls]) => (
          <div key={label as string} className={`rounded-lg border p-4 ${cls}`}>
            <p className="text-sm text-gray-500">{label}</p>
            <p className="text-2xl font-bold">{Math.round(+val)}<span className="text-sm font-normal ml-1">{unit}</span></p>
          </div>
        ))}
      </div>

      {showForm && (
        <form onSubmit={handleSubmit} className="bg-white rounded-lg shadow p-6 mb-6 space-y-3">
          <h2 className="text-xl font-semibold">Log Meal</h2>
          <div className="grid grid-cols-2 gap-3">
            <select value={form.meal_type} onChange={e => setForm({...form, meal_type: e.target.value})} className="px-3 py-2 border rounded-lg">
              {mealTypes.map(t => <option key={t}>{t}</option>)}
            </select>
            <input placeholder="Food item" value={form.food_item} onChange={e => setForm({...form, food_item: e.target.value})}
              className="px-3 py-2 border rounded-lg" required />
            <input type="number" placeholder="Quantity" value={form.quantity} onChange={e => setForm({...form, quantity: e.target.value})}
              className="px-3 py-2 border rounded-lg" required />
            <input placeholder="Unit (g, ml, ...)" value={form.unit} onChange={e => setForm({...form, unit: e.target.value})}
              className="px-3 py-2 border rounded-lg" required />
            <input type="number" placeholder="Calories (kcal)" value={form.calories} onChange={e => setForm({...form, calories: e.target.value})}
              className="px-3 py-2 border rounded-lg" required />
            <input type="number" placeholder="Protein (g)" value={form.protein} onChange={e => setForm({...form, protein: e.target.value})}
              className="px-3 py-2 border rounded-lg" step="0.1" />
            <input type="number" placeholder="Carbs (g)" value={form.carbs} onChange={e => setForm({...form, carbs: e.target.value})}
              className="px-3 py-2 border rounded-lg" step="0.1" />
            <input type="number" placeholder="Fats (g)" value={form.fats} onChange={e => setForm({...form, fats: e.target.value})}
              className="px-3 py-2 border rounded-lg" step="0.1" />
          </div>
          <div className="flex gap-2">
            <button type="submit" className="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Save</button>
            <button type="button" onClick={() => setShowForm(false)} className="bg-gray-200 px-4 py-2 rounded-lg">Cancel</button>
          </div>
        </form>
      )}

      <div className="space-y-4">
        {groupedMeals.length === 0 ? <p className="text-center text-gray-500 py-12">No meals logged for this day.</p> :
          groupedMeals.map(group => (
            <div key={group.type} className="bg-white rounded-lg shadow">
              <div className="px-5 py-3 border-b bg-gray-50 rounded-t-lg font-semibold">{group.type}</div>
              {group.meals.map(meal => (
                <div key={meal.id} className="px-5 py-3 flex justify-between items-center border-b last:border-0">
                  <div>
                    <p className="font-medium">{meal.food_item} <span className="text-gray-400 text-sm">({meal.quantity} {meal.unit})</span></p>
                    <p className="text-sm text-gray-500">{meal.calories} kcal • P: {meal.protein}g • C: {meal.carbs}g • F: {meal.fats}g</p>
                  </div>
                  <button onClick={() => deleteMeal(meal.id)} className="text-red-400 hover:text-red-600 text-sm">✕</button>
                </div>
              ))}
            </div>
          ))}
      </div>
    </div>
  );
}
