import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import axios from 'axios';

const API = process.env.REACT_APP_API_URL || 'http://localhost:8000/api';

export default function ForgotPasswordPage() {
  const [email, setEmail] = useState('');
  const [loading, setLoading] = useState(false);
  const [sent, setSent] = useState(false);
  const [devLink, setDevLink] = useState('');
  const [error, setError] = useState('');

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setError('');
    try {
      const res = await axios.post(`${API}/auth/forgot-password`, { email });
      setSent(true);
      if (res.data.dev_reset_link) setDevLink(res.data.dev_reset_link);
    } catch (err: any) {
      setError(err?.response?.data?.message || 'Something went wrong.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-600 to-blue-800">
      <div className="bg-white rounded-lg shadow-lg p-8 w-full max-w-md">
        <h1 className="text-2xl font-bold text-center mb-2 text-blue-600">Forgot Password</h1>
        <p className="text-center text-gray-500 mb-6 text-sm">Enter your email and we'll send you a reset link.</p>

        {sent ? (
          <div className="space-y-4">
            <div className="bg-green-50 border border-green-300 text-green-700 px-4 py-3 rounded-lg text-sm">
              ✓ If an account with <strong>{email}</strong> exists, a reset link has been sent.
            </div>

            {devLink && (
              <div className="bg-yellow-50 border border-yellow-300 rounded-lg p-4">
                <p className="text-xs font-bold text-yellow-700 mb-2">DEV MODE — Reset Link (no email configured yet):</p>
                <a href={devLink} className="text-blue-600 text-xs break-all hover:underline">{devLink}</a>
              </div>
            )}

            <p className="text-center text-sm text-gray-500">
              <Link to="/login" className="text-blue-600 hover:underline">← Back to Login</Link>
            </p>
          </div>
        ) : (
          <form onSubmit={handleSubmit} className="space-y-4">
            {error && <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded text-sm">{error}</div>}
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
              <input
                type="email" value={email} onChange={e => setEmail(e.target.value)}
                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="your@email.com" required
              />
            </div>
            <button type="submit" disabled={loading}
              className="w-full bg-blue-600 text-white py-2 rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50">
              {loading ? 'Sending...' : 'Send Reset Link'}
            </button>
            <p className="text-center text-sm text-gray-500">
              <Link to="/login" className="text-blue-600 hover:underline">← Back to Login</Link>
            </p>
          </form>
        )}
      </div>
    </div>
  );
}
