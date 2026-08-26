import { useState, type FormEvent } from 'react';
import { Link, useLocation, useNavigate } from 'react-router';
import { LogIn } from 'lucide-react';
import { login } from '../api/auth';
import { extractApiError } from '../api/client';
import { useAuth } from '../auth/useAuth';
import { Alert, Button, Card, Field, Input } from '../components/ui';
import { parseJwt } from '../lib/utils';

interface LoginLocationState {
  from?: string;
  registered?: boolean;
}

export function LoginPage() {
  const { login: storeToken } = useAuth();
  const navigate = useNavigate();
  const location = useLocation();
  const state = (location.state ?? {}) as LoginLocationState;

  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const redirectAfterLogin = (token: string) => {
    if (state.from) {
      navigate(state.from, { replace: true });
      return;
    }
    const isAdmin = parseJwt(token)?.roles.includes('ROLE_ADMIN') ?? false;
    navigate(isAdmin ? '/admin/users' : '/profile', { replace: true });
  };

  const handleSubmit = async (event: FormEvent) => {
    event.preventDefault();
    setSubmitting(true);
    setError(null);

    try {
      const response = await login(email, password);
      storeToken(response.token);
      redirectAfterLogin(response.token);
    } catch (err) {
      setError(extractApiError(err).message);
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <div className="flex min-h-screen items-center justify-center bg-slate-100 px-4">
      <div className="w-full max-w-md">
        <div className="mb-6 text-center">
          <h1 className="text-2xl font-bold text-slate-900">Starter Kit</h1>
          <p className="mt-1 text-sm text-slate-500">Connexion à votre espace</p>
        </div>

        {state.registered && (
          <div className="mb-4">
            <Alert kind="success">Compte créé avec succès. Connectez-vous pour continuer.</Alert>
          </div>
        )}

        <Card>
          <form onSubmit={handleSubmit} className="space-y-4">
            {error && <Alert kind="error">{error}</Alert>}

            <Field label="Adresse e-mail" htmlFor="email">
              <Input
                id="email"
                type="email"
                autoComplete="email"
                required
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                placeholder="vous@exemple.fr"
              />
            </Field>

            <Field label="Mot de passe" htmlFor="password">
              <Input
                id="password"
                type="password"
                autoComplete="current-password"
                required
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                placeholder="••••••••"
              />
            </Field>

            <Button type="submit" className="w-full" loading={submitting} disabled={submitting}>
              <LogIn className="h-4 w-4" aria-hidden />
              Se connecter
            </Button>
          </form>
        </Card>

        <p className="mt-4 text-center text-sm text-slate-600">
          Pas encore de compte ?{' '}
          <Link to="/register" className="font-semibold text-indigo-600 hover:text-indigo-500">
            Créer un compte
          </Link>
        </p>
      </div>
    </div>
  );
}