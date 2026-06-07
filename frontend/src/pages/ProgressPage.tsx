import React, { useState, useEffect } from 'react';
import axios from 'axios';

const API = process.env.REACT_APP_API_URL || 'http://localhost:8000/api';

interface ExLog { id: number; exercise_name: string; set_number: number; reps: number; weight: number; weight_unit: string; rir: number | null; }
interface Workout { id: number; session_name: string; workout_date: string; duration_minutes: number | null; energy_level: number | null; notes: string | null; exercise_logs: ExLog[]; }

function groupByExercise(logs: ExLog[]): { name: string; sets: ExLog[] }[] {
  const map: Record<string, ExLog[]> = {};
  logs.forEach(l => { if (!map[l.exercise_name]) map[l.exercise_name] = []; map[l.exercise_name].push(l); });
  return Object.entries(map).map(([name, sets]) => ({ name, sets }));
}

export default function ProgressPage() {
  const [workouts, setWorkouts] = useState<Workout[]>([]);
  const [expanded, setExpanded] = useState<number | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    axios.get(`${API}/workouts/history`, { params: { per_page: 50 } })
      .then(r => setWorkouts(r.data.data || []))
      .finally(() => setLoading(false));
  }, []);

  // Group workouts by date
  const byDate: Record<string, Workout[]> = {};
  workouts.forEach(w => {
    const d = w.workout_date?.slice(0, 10) || 'Unbekannt';
    if (!byDate[d]) byDate[d] = [];
    byDate[d].push(w);
  });
  const dates = Object.keys(byDate).sort((a, b) => b.localeCompare(a));

  const totalVolume = (logs: ExLog[]) =>
    logs.reduce((s, l) => s + (l.reps || 0) * (l.weight || 0), 0).toFixed(1);

  return (
    <div className="container mx-auto p-4 md:p-6 max-w-3xl">
      <h1 className="text-2xl md:text-3xl font-bold mb-2">Fortschritt</h1>
      <p className="text-gray-500 mb-6 text-sm">Deine absolvierten Workouts mit allen Sets und Wiederholungen</p>

      {loading && <p className="text-center text-gray-400 py-12">Lade...</p>}

      {!loading && workouts.length === 0 && (
        <div className="text-center py-16 bg-white rounded-xl shadow">
          <p className="text-gray-400 text-lg">Noch keine Workouts aufgezeichnet.</p>
          <p className="text-gray-300 text-sm mt-1">Starte deinen ersten Workout im Tracker!</p>
        </div>
      )}

      <div className="space-y-6">
        {dates.map(date => (
          <div key={date}>
            <h2 className="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-2 px-1">
              {new Date(date).toLocaleDateString('de-DE', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })}
            </h2>
            <div className="space-y-2">
              {byDate[date].map(w => {
                const groups = groupByExercise(w.exercise_logs || []);
                const isOpen = expanded === w.id;
                return (
                  <div key={w.id} className="bg-white rounded-xl shadow overflow-hidden">
                    {/* Header */}
                    <button onClick={() => setExpanded(isOpen ? null : w.id)}
                      className="w-full text-left px-4 py-4 flex justify-between items-center hover:bg-gray-50 transition-colors">
                      <div>
                        <p className="font-semibold text-base">{w.session_name}</p>
                        <div className="flex flex-wrap gap-3 mt-1 text-xs text-gray-400">
                          {w.duration_minutes && <span>{w.duration_minutes} min</span>}
                          {w.energy_level && <span>Energie: {w.energy_level}/10</span>}
                          <span>{(w.exercise_logs || []).length} Sets</span>
                          <span>{groups.length} &Uuml;bungen</span>
                          {Number(totalVolume(w.exercise_logs || [])) > 0 && (
                            <span>Volumen: {totalVolume(w.exercise_logs || [])} kg</span>
                          )}
                        </div>
                      </div>
                      <span className="text-gray-400 text-lg ml-3">{isOpen ? '▲' : '▼'}</span>
                    </button>

                    {/* Detail */}
                    {isOpen && (
                      <div className="border-t divide-y">
                        {groups.length === 0 && (
                          <p className="px-4 py-3 text-sm text-gray-400 italic">Keine Sets geloggt.</p>
                        )}
                        {groups.map(g => (
                          <div key={g.name} className="px-4 py-3">
                            <p className="font-medium text-sm mb-2">{g.name}</p>
                            <div className="overflow-x-auto">
                              <table className="w-full text-sm">
                                <thead>
                                  <tr className="text-xs text-gray-400 text-left">
                                    <th className="pb-1 pr-4">Satz</th>
                                    <th className="pb-1 pr-4">Wdh.</th>
                                    <th className="pb-1 pr-4">Gewicht</th>
                                    <th className="pb-1">RIR</th>
                                  </tr>
                                </thead>
                                <tbody>
                                  {g.sets.sort((a, b) => a.set_number - b.set_number).map(s => (
                                    <tr key={s.id} className="border-t border-gray-50">
                                      <td className="py-1 pr-4 text-gray-600">{s.set_number}</td>
                                      <td className="py-1 pr-4 font-medium">{s.reps}</td>
                                      <td className="py-1 pr-4 text-gray-600">{s.weight ? `${s.weight} ${s.weight_unit}` : '-'}</td>
                                      <td className="py-1 text-gray-600">{s.rir ?? '-'}</td>
                                    </tr>
                                  ))}
                                </tbody>
                              </table>
                            </div>
                          </div>
                        ))}
                        {w.notes && (
                          <div className="px-4 py-3 bg-yellow-50">
                            <p className="text-xs text-yellow-700 font-medium">Notizen</p>
                            <p className="text-sm text-gray-600 mt-0.5">{w.notes}</p>
                          </div>
                        )}
                      </div>
                    )}
                  </div>
                );
              })}
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}
