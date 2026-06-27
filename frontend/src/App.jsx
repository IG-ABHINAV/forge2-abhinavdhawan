import React, { useCallback, useEffect, useMemo, useState } from 'react';
import axios from 'axios';
import './index.css';

const rawApiUrl = import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000';
axios.defaults.baseURL = rawApiUrl.endsWith('/api') ? rawApiUrl : `${rawApiUrl.replace(/\/$/, '')}/api`;

const statuses = ['open', 'in_progress', 'resolved', 'closed'];
const priorities = ['low', 'medium', 'high', 'urgent'];

function pretty(value) {
  return String(value || '').replace(/_/g, ' ');
}

function Toast({ msg, type, onDone }) {
  useEffect(() => {
    const timer = setTimeout(onDone, 2800);
    return () => clearTimeout(timer);
  }, [onDone]);

  return <div className={`toast ${type}`}>{type === 'success' ? 'OK' : 'Error'}: {msg}</div>;
}

function PriorityBadge({ p }) {
  const map = { urgent: 'badge-urgent', high: 'badge-high', medium: 'badge-medium', low: 'badge-low' };
  return <span className={`badge ${map[p] || 'badge-low'}`}>{pretty(p)}</span>;
}

function StatusBadge({ s }) {
  const map = { open: 'badge-open', in_progress: 'badge-in_progress', resolved: 'badge-resolved', closed: 'badge-closed' };
  return <span className={`badge ${map[s] || 'badge-low'}`}>{pretty(s)}</span>;
}

function AuthPage({ onLogin }) {
  const [mode, setMode] = useState('login');
  const [email, setEmail] = useState('admin@acme.test');
  const [pass, setPass] = useState('password');
  const [name, setName] = useState('');
  const [org, setOrg] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  const submit = async (event) => {
    event.preventDefault();
    setError('');
    setLoading(true);

    try {
      const url = mode === 'login' ? '/v1/login' : '/v1/register';
      const body = mode === 'login'
        ? { email, password: pass }
        : { org_name: org, name, email, password: pass };
      const res = await axios.post(url, body);
      onLogin(res.data.access_token, res.data.user);
    } catch (err) {
      setError(err.response?.data?.message || (mode === 'login' ? 'Login failed.' : 'Registration failed.'));
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="auth-bg">
      <div className="auth-card">
        <div className="logo-mark">
          <div className="logo-icon">P</div>
          <span className="logo-name">PulseDesk</span>
        </div>
        <p className="auth-subtitle">Multi-tenant support desk for fast-moving teams</p>

        {error && <div className="error-box">{error}</div>}

        <form onSubmit={submit}>
          {mode === 'register' && (
            <>
              <div className="form-group">
                <label className="form-label">Organization Name</label>
                <input className="form-input" required value={org} onChange={(e) => setOrg(e.target.value)} />
              </div>
              <div className="form-group">
                <label className="form-label">Full Name</label>
                <input className="form-input" required value={name} onChange={(e) => setName(e.target.value)} />
              </div>
            </>
          )}
          <div className="form-group">
            <label className="form-label">Email Address</label>
            <input className="form-input" type="email" required value={email} onChange={(e) => setEmail(e.target.value)} />
          </div>
          <div className="form-group">
            <label className="form-label">Password</label>
            <input className="form-input" type="password" required value={pass} onChange={(e) => setPass(e.target.value)} />
          </div>
          <button className="btn-primary" type="submit" disabled={loading}>
            {loading ? 'Please wait...' : mode === 'login' ? 'Sign In' : 'Create Account'}
          </button>
        </form>

        <div className="auth-toggle">
          {mode === 'login'
            ? <>No account? <span onClick={() => { setMode('register'); setError(''); }}>Register organization</span></>
            : <>Have an account? <span onClick={() => { setMode('login'); setError(''); }}>Sign in</span></>}
        </div>
        <div className="demo-note">Demo: admin@acme.test / password</div>
      </div>
    </div>
  );
}

function CreateTicketForm({ onCreated, onToast }) {
  const [title, setTitle] = useState('');
  const [desc, setDesc] = useState('');
  const [priority, setPriority] = useState('medium');
  const [loading, setLoading] = useState(false);

  const submit = async (event) => {
    event.preventDefault();
    setLoading(true);
    try {
      await axios.post('/v1/tickets', { title, description: desc, priority });
      setTitle('');
      setDesc('');
      setPriority('medium');
      onToast('Ticket created successfully.', 'success');
      onCreated();
    } catch (err) {
      onToast(err.response?.data?.message || 'Failed to create ticket.', 'error');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="card">
      <div className="card-header">
        <span className="card-title">New Ticket</span>
      </div>
      <form onSubmit={submit}>
        <div className="form-group">
          <label className="form-label">Subject</label>
          <input className="form-input" required placeholder="Brief summary of the issue" value={title} onChange={(e) => setTitle(e.target.value)} />
        </div>
        <div className="form-group">
          <label className="form-label">Description</label>
          <textarea className="form-textarea" required placeholder="Describe the problem in detail" value={desc} onChange={(e) => setDesc(e.target.value)} />
        </div>
        <div className="form-group">
          <label className="form-label">Priority</label>
          <select className="form-select" value={priority} onChange={(e) => setPriority(e.target.value)}>
            {priorities.map((item) => <option key={item} value={item}>{pretty(item)}</option>)}
          </select>
        </div>
        <button className="btn-primary" type="submit" disabled={loading}>
          {loading ? 'Submitting...' : 'Submit Ticket'}
        </button>
      </form>
    </div>
  );
}

function TicketDetail({ ticket, onUpdated, onToast }) {
  const [comment, setComment] = useState('');
  const [internal, setInternal] = useState(false);
  const [saving, setSaving] = useState(false);

  if (!ticket) {
    return (
      <div className="card detail-empty">
        <div className="empty-icon">PD</div>
        <div className="empty-text">Select a ticket to inspect, update, and comment.</div>
      </div>
    );
  }

  const updateTicket = async (patch) => {
    setSaving(true);
    try {
      const res = await axios.patch(`/v1/tickets/${ticket.id}`, patch);
      onUpdated({ ...ticket, ...res.data });
      onToast('Ticket updated.', 'success');
    } catch (err) {
      onToast(err.response?.data?.message || 'Could not update ticket.', 'error');
    } finally {
      setSaving(false);
    }
  };

  const addComment = async (event) => {
    event.preventDefault();
    if (!comment.trim()) return;
    setSaving(true);
    try {
      const res = await axios.post(`/v1/tickets/${ticket.id}/comments`, { body: comment, is_internal: internal });
      onUpdated({ ...ticket, comments: [...(ticket.comments || []), res.data] });
      setComment('');
      setInternal(false);
      onToast('Comment added.', 'success');
    } catch (err) {
      onToast(err.response?.data?.message || 'Could not add comment.', 'error');
    } finally {
      setSaving(false);
    }
  };

  return (
    <div className="card detail-card">
      <div className="detail-head">
        <div>
          <div className="eyebrow">Ticket #{ticket.id}</div>
          <h2>{ticket.title}</h2>
          <p>{ticket.description}</p>
        </div>
        <div className="badges">
          <PriorityBadge p={ticket.priority} />
          <StatusBadge s={ticket.status} />
        </div>
      </div>

      <div className="detail-grid">
        <div className="form-group">
          <label className="form-label">Status</label>
          <select className="form-select" disabled={saving} value={ticket.status} onChange={(e) => updateTicket({ status: e.target.value })}>
            {statuses.map((item) => <option key={item} value={item}>{pretty(item)}</option>)}
          </select>
        </div>
        <div className="form-group">
          <label className="form-label">Priority</label>
          <select className="form-select" disabled={saving} value={ticket.priority} onChange={(e) => updateTicket({ priority: e.target.value })}>
            {priorities.map((item) => <option key={item} value={item}>{pretty(item)}</option>)}
          </select>
        </div>
      </div>

      <div className="meta-strip">
        <span>Requester: {ticket.user?.name || 'Unknown'}</span>
        <span>Assignee: {ticket.assignee?.name || 'Unassigned'}</span>
        <span>Created: {new Date(ticket.created_at).toLocaleString()}</span>
      </div>

      <div className="comments">
        <div className="card-title">Conversation</div>
        {(ticket.comments || []).length === 0 ? (
          <div className="empty-text small">No comments yet.</div>
        ) : (
          ticket.comments.map((item) => (
            <div className="comment" key={item.id}>
              <div className="comment-top">
                <strong>{item.user?.name || 'User'}</strong>
                {item.is_internal && <span>internal</span>}
              </div>
              <p>{item.body}</p>
            </div>
          ))
        )}
      </div>

      <form onSubmit={addComment} className="comment-form">
        <textarea className="form-textarea" placeholder="Write a reply or internal note" value={comment} onChange={(e) => setComment(e.target.value)} />
        <label className="check-row">
          <input type="checkbox" checked={internal} onChange={(e) => setInternal(e.target.checked)} />
          Internal note
        </label>
        <button className="btn-primary" type="submit" disabled={saving || !comment.trim()}>Add Comment</button>
      </form>
    </div>
  );
}

function Dashboard({ user, tickets, dashboard, selectedTicket, loading, onRefresh, onSelect, onUpdated, onToast, onLogout }) {
  const [filter, setFilter] = useState('all');
  const [search, setSearch] = useState('');

  const stats = dashboard || {
    total_tickets: tickets.length,
    by_status: {
      open: tickets.filter((t) => t.status === 'open').length,
      in_progress: tickets.filter((t) => t.status === 'in_progress').length,
      resolved: tickets.filter((t) => t.status === 'resolved').length,
      closed: tickets.filter((t) => t.status === 'closed').length,
    },
    by_priority: {
      urgent: tickets.filter((t) => t.priority === 'urgent').length,
    },
    sla_breached: tickets.filter((t) => t.sla_breached).length,
  };

  const filtered = useMemo(() => {
    return tickets.filter((ticket) => {
      const matchesFilter =
        filter === 'all' ||
        ticket.status === filter ||
        ticket.priority === filter ||
        (filter === 'sla' && ticket.sla_breached);
      const haystack = `${ticket.title} ${ticket.description} ${ticket.user?.name || ''}`.toLowerCase();
      return matchesFilter && haystack.includes(search.toLowerCase());
    });
  }, [filter, search, tickets]);

  const initials = (user?.name || 'U').split(' ').map((n) => n[0]).join('').toUpperCase().slice(0, 2);

  return (
    <div className="app-shell">
      <aside className="sidebar">
        <div className="sidebar-logo">
          <div className="logo-icon">P</div>
          <span className="logo-name">PulseDesk</span>
        </div>

        <div className="nav-section">Main</div>
        <button className="nav-item active"><span className="nav-icon">T</span> Tickets <span className="nav-badge">{stats.by_status?.open || 0}</span></button>
        <button className="nav-item"><span className="nav-icon">D</span> Dashboard</button>
        <button className="nav-item"><span className="nav-icon">A</span> AI Agents</button>

        <div className="divider" />
        <div className="nav-section">Workspace</div>
        <button className="nav-item"><span className="nav-icon">K</span> Knowledge Base</button>
        <button className="nav-item"><span className="nav-icon">S</span> Settings</button>

        <div className="sidebar-bottom">
          {user && (
            <div className="user-card">
              <div className="user-avatar">{initials}</div>
              <div className="user-info">
                <div className="user-name">{user.name}</div>
                <div className="user-role">{user.organization?.name}</div>
              </div>
              <button className="btn-logout" onClick={onLogout} title="Sign out">Exit</button>
            </div>
          )}
        </div>
      </aside>

      <main className="main-content">
        <div className="topbar">
          <div>
            <div className="page-title">Ticket Queue</div>
            <div className="page-subtitle">Manage support requests for {user?.organization?.name || 'your organization'}</div>
          </div>
          <button className="btn-sm" onClick={onRefresh}>Refresh</button>
        </div>

        <div className="stats-grid">
          {[
            { label: 'Total Tickets', value: stats.total_tickets, cls: 'blue' },
            { label: 'Open', value: stats.by_status?.open || 0, cls: 'green' },
            { label: 'Urgent', value: stats.by_priority?.urgent || 0, cls: 'red' },
            { label: 'SLA Breached', value: stats.sla_breached || 0, cls: 'amber' },
          ].map((item) => (
            <div className="stat-card" key={item.label}>
              <div className="stat-label">{item.label}</div>
              <div className={`stat-value ${item.cls}`}>{item.value}</div>
              <div className="stat-change">current workspace</div>
            </div>
          ))}
        </div>

        <div className="workspace-grid">
          <section>
            <div className="filter-row">
              {['all', 'open', 'in_progress', 'urgent', 'resolved', 'sla'].map((item) => (
                <button key={item} className={`btn-sm ${filter === item ? 'active' : ''}`} onClick={() => setFilter(item)}>
                  {pretty(item)}
                </button>
              ))}
            </div>
            <input className="form-input search-input" placeholder="Search tickets" value={search} onChange={(e) => setSearch(e.target.value)} />

            <div className="card ticket-card">
              <div className="card-header">
                <span className="card-title">All Tickets</span>
                <span className="card-count">{filtered.length} showing</span>
              </div>
              {loading ? (
                [1, 2, 3].map((item) => <div key={item} className="skeleton skeleton-ticket" />)
              ) : filtered.length === 0 ? (
                <div className="empty-state">
                  <div className="empty-icon">PD</div>
                  <div className="empty-text">No tickets found.</div>
                </div>
              ) : (
                <div className="ticket-list">
                  {filtered.map((ticket) => (
                    <button className={`ticket-item ${selectedTicket?.id === ticket.id ? 'selected' : ''}`} key={ticket.id} onClick={() => onSelect(ticket)}>
                      <div className="ticket-top">
                        <div>
                          <div className="ticket-title">{ticket.title}</div>
                          <div className="ticket-meta">#{ticket.id} - {ticket.user?.name || 'Unknown'} - {new Date(ticket.created_at).toLocaleDateString()}</div>
                        </div>
                        <div className="badges">
                          <PriorityBadge p={ticket.priority} />
                          <StatusBadge s={ticket.status} />
                        </div>
                      </div>
                      <p className="ticket-desc">{ticket.description}</p>
                    </button>
                  ))}
                </div>
              )}
            </div>
          </section>

          <section className="side-stack">
            <CreateTicketForm onCreated={onRefresh} onToast={onToast} />
            <TicketDetail ticket={selectedTicket} onUpdated={onUpdated} onToast={onToast} />
          </section>
        </div>
      </main>
    </div>
  );
}

export default function App() {
  const [token, setToken] = useState(() => localStorage.getItem('pd_token') || '');
  const [user, setUser] = useState(null);
  const [tickets, setTickets] = useState([]);
  const [dashboard, setDashboard] = useState(null);
  const [selectedTicketId, setSelectedTicketId] = useState(null);
  const [loading, setLoading] = useState(false);
  const [toast, setToast] = useState(null);

  useEffect(() => {
    if (token) {
      axios.defaults.headers.common.Authorization = `Bearer ${token}`;
    } else {
      delete axios.defaults.headers.common.Authorization;
    }
  }, [token]);

  const selectedTicket = useMemo(
    () => tickets.find((ticket) => ticket.id === selectedTicketId) || tickets[0] || null,
    [selectedTicketId, tickets],
  );

  const showToast = useCallback((msg, type = 'success') => {
    setToast({ msg, type });
  }, []);

  const handleLogout = useCallback(async () => {
    try {
      if (token) await axios.post('/v1/logout');
    } catch {
      // Local logout should still complete when the token is stale.
    }
    localStorage.removeItem('pd_token');
    setToken('');
    setUser(null);
    setTickets([]);
    setDashboard(null);
    setSelectedTicketId(null);
  }, [token]);

  const fetchUser = useCallback(async () => {
    try {
      const res = await axios.get('/v1/me');
      setUser(res.data);
    } catch {
      handleLogout();
    }
  }, [handleLogout]);

  const fetchData = useCallback(async () => {
    setLoading(true);
    try {
      const [ticketRes, dashboardRes] = await Promise.all([
        axios.get('/v1/tickets'),
        axios.get('/v1/dashboard'),
      ]);
      const nextTickets = ticketRes.data.data || [];
      setTickets(nextTickets);
      setDashboard(dashboardRes.data);
      setSelectedTicketId((current) => current || nextTickets[0]?.id || null);
    } catch (err) {
      showToast(err.response?.data?.message || 'Could not load workspace.', 'error');
    } finally {
      setLoading(false);
    }
  }, [showToast]);

  useEffect(() => {
    if (token) {
      fetchUser();
      fetchData();
    }
  }, [fetchData, fetchUser, token]);

  const handleLogin = (nextToken, nextUser) => {
    localStorage.setItem('pd_token', nextToken);
    setToken(nextToken);
    if (nextUser) setUser(nextUser);
  };

  const handleTicketUpdated = (updatedTicket) => {
    setTickets((current) => current.map((ticket) => (ticket.id === updatedTicket.id ? updatedTicket : ticket)));
    setSelectedTicketId(updatedTicket.id);
    fetchData();
  };

  if (!token) return <AuthPage onLogin={handleLogin} />;

  return (
    <>
      <Dashboard
        user={user}
        tickets={tickets}
        dashboard={dashboard}
        selectedTicket={selectedTicket}
        loading={loading}
        onRefresh={fetchData}
        onSelect={(ticket) => setSelectedTicketId(ticket.id)}
        onUpdated={handleTicketUpdated}
        onToast={showToast}
        onLogout={handleLogout}
      />
      {toast && <Toast msg={toast.msg} type={toast.type} onDone={() => setToast(null)} />}
    </>
  );
}
