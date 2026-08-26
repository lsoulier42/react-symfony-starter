import { Outlet, Link, useNavigate } from 'react-router';
import { LogOut, Shield, UserRound } from 'lucide-react';
import { useAuth } from '../auth/useAuth';
import { Badge, Button } from '../components/ui';

/** Authenticated app shell: header with role-aware navigation + logout, then the page content. */
export function AppLayout() {
  const { email, roles, logout } = useAuth();
  const navigate = useNavigate();
  const isAdmin = roles.includes('ROLE_ADMIN');

  const handleLogout = () => {
    logout();
    navigate('/login');
  };

  return (
    <div className="min-h-screen bg-slate-100">
      <header className="border-b border-slate-200 bg-white">
        <div className="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6">
          <div className="flex items-center gap-1 sm:gap-2">
            <Link
              to="/profile"
              className="rounded-md px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100"
            >
              Profil
            </Link>
            {isAdmin && (
              <Link
                to="/admin/users"
                className="flex items-center gap-1.5 rounded-md px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100"
              >
                <Shield className="h-4 w-4" aria-hidden />
                Utilisateurs
              </Link>
            )}
          </div>

          <div className="flex items-center gap-3">
            <div className="hidden items-center gap-2 sm:flex">
              <UserRound className="h-4 w-4 text-slate-400" aria-hidden />
              <span className="text-sm text-slate-600">{email}</span>
              {isAdmin && <Badge tone="indigo">Admin</Badge>}
            </div>
            <Button variant="ghost" size="sm" onClick={handleLogout}>
              <LogOut className="h-4 w-4" aria-hidden />
              Déconnexion
            </Button>
          </div>
        </div>
      </header>

      <main className="mx-auto max-w-6xl px-4 py-8 sm:px-6">
        <Outlet />
      </main>
    </div>
  );
}