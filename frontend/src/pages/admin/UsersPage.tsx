import { useState, type FormEvent } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { RefreshCw, Search, Shield, ShieldOff, Trash2, Power, PowerOff } from 'lucide-react';
import { deleteUser, getUsers, patchUser } from '../../api/users';
import { extractApiError } from '../../api/client';
import { useAuth } from '../../auth/useAuth';
import { Alert, Badge, Button, ErrorState, Input, Spinner } from '../../components/ui';

const ITEMS_PER_PAGE = 10;

type StatusFilter = 'all' | 'active' | 'inactive';

export function UsersPage() {
  const queryClient = useQueryClient();
  const { email: currentEmail } = useAuth();

  const [page, setPage] = useState(1);
  const [searchInput, setSearchInput] = useState('');
  const [search, setSearch] = useState('');
  const [status, setStatus] = useState<StatusFilter>('all');
  const [error, setError] = useState<string | null>(null);

  const usersQuery = useQuery({
    queryKey: ['users', { page, search, status }],
    queryFn: () =>
      getUsers({
        page,
        itemsPerPage: ITEMS_PER_PAGE,
        email: search || undefined,
        isActive: status === 'all' ? undefined : status === 'active',
      }),
  });

  const invalidate = () => {
    void queryClient.invalidateQueries({ queryKey: ['users'] });
  };

  const mutation = useMutation({
    mutationFn: ({ id, input }: { id: number; input: Parameters<typeof patchUser>[1] }) => patchUser(id, input),
    onSuccess: invalidate,
    onError: (err) => setError(extractApiError(err).message),
  });

  const deleteMutation = useMutation({
    mutationFn: (id: number) => deleteUser(id),
    onSuccess: invalidate,
    onError: (err) => setError(extractApiError(err).message),
  });

  const handleSearchSubmit = (event: FormEvent) => {
    event.preventDefault();
    setPage(1);
    setSearch(searchInput.trim());
  };

  const handleStatusChange = (next: StatusFilter) => {
    setStatus(next);
    setPage(1);
  };

  const isSelf = (userEmail: string) => userEmail === currentEmail;

  const toggleActive = (id: number, isActive: boolean) => {
    setError(null);
    mutation.mutate({ id, input: { isActive: !isActive } });
  };

  const toggleAdmin = (id: number, roles: string[]) => {
    setError(null);
    const isAdmin = roles.includes('ROLE_ADMIN');
    mutation.mutate({ id, input: { roles: isAdmin ? ['ROLE_USER'] : ['ROLE_ADMIN'] } });
  };

  const handleDelete = (id: number, userEmail: string) => {
    setError(null);
    if (!window.confirm(`Supprimer définitivement le compte ${userEmail} ?`)) {
      return;
    }
    deleteMutation.mutate(id);
  };

  const data = usersQuery.data;
  const totalPages = data ? Math.max(1, Math.ceil(data.totalItems / ITEMS_PER_PAGE)) : 1;

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-xl font-bold text-slate-900">Utilisateurs inscrits</h1>
        <p className="mt-1 text-sm text-slate-500">
          {data ? `${data.totalItems} utilisateur(s) inscrit(s)` : 'Gestion des comptes inscrits.'}
        </p>
      </div>

      <form onSubmit={handleSearchSubmit} className="flex flex-col gap-3 sm:flex-row sm:items-center">
        <div className="relative flex-1">
          <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" aria-hidden />
          <Input
            type="search"
            placeholder="Rechercher par e-mail…"
            value={searchInput}
            onChange={(e) => setSearchInput(e.target.value)}
            className="pl-9"
          />
        </div>
        <Button type="submit" variant="secondary">
          Rechercher
        </Button>
        <div className="flex gap-1 rounded-md bg-white p-1 ring-1 ring-inset ring-slate-300">
          {(
            [
              ['all', 'Tous'],
              ['active', 'Actifs'],
              ['inactive', 'Désactivés'],
            ] as Array<[StatusFilter, string]>
          ).map(([value, label]) => (
            <button
              key={value}
              type="button"
              onClick={() => handleStatusChange(value)}
              className={`rounded px-3 py-1.5 text-sm font-medium transition ${
                status === value ? 'bg-indigo-600 text-white' : 'text-slate-600 hover:bg-slate-100'
              }`}
            >
              {label}
            </button>
          ))}
        </div>
      </form>

      {error && <Alert kind="error">{error}</Alert>}

      {usersQuery.isLoading && <Spinner label="Chargement des utilisateurs…" />}

      {usersQuery.isError && <ErrorState message="Impossible de charger les utilisateurs." />}

      {data && (
        <>
          <div className="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
            <div className="overflow-x-auto">
              <table className="min-w-full divide-y divide-slate-200 text-sm">
                <thead>
                  <tr className="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <th className="px-4 py-3">Utilisateur</th>
                    <th className="px-4 py-3">Rôle</th>
                    <th className="px-4 py-3">Statut</th>
                    <th className="px-4 py-3 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-200">
                  {data.member.map((user) => {
                    const self = isSelf(user.email);
                    const isAdmin = user.roles.includes('ROLE_ADMIN');
                    return (
                      <tr key={user.id} className="hover:bg-slate-50">
                        <td className="px-4 py-3">
                          <div className="font-medium text-slate-900">
                            {user.firstName || user.lastName ? `${user.firstName ?? ''} ${user.lastName ?? ''}`.trim() : '—'}
                            {self && <span className="ml-2 text-xs text-slate-400">(vous)</span>}
                          </div>
                          <div className="text-xs text-slate-500">{user.email}</div>
                        </td>
                        <td className="px-4 py-3">
                          <Badge tone={isAdmin ? 'indigo' : 'slate'}>{isAdmin ? 'Administrateur' : 'Utilisateur'}</Badge>
                        </td>
                        <td className="px-4 py-3">
                          {user.isActive ? <Badge tone="emerald">Actif</Badge> : <Badge tone="red">Désactivé</Badge>}
                        </td>
                        <td className="px-4 py-3">
                          <div className="flex items-center justify-end gap-1">
                            <Button
                              variant="ghost"
                              size="sm"
                              title={user.isActive ? 'Désactiver le compte' : 'Réactiver le compte'}
                              disabled={self || mutation.isPending}
                              onClick={() => toggleActive(user.id, user.isActive)}
                            >
                              {user.isActive ? <PowerOff className="h-4 w-4" /> : <Power className="h-4 w-4" />}
                            </Button>
                            <Button
                              variant="ghost"
                              size="sm"
                              title={isAdmin ? 'Retirer le rôle administrateur' : 'Promouvoir administrateur'}
                              disabled={self || mutation.isPending}
                              onClick={() => toggleAdmin(user.id, user.roles)}
                            >
                              {isAdmin ? (
                                <ShieldOff className="h-4 w-4 text-amber-600" />
                              ) : (
                                <Shield className="h-4 w-4" />
                              )}
                            </Button>
                            <Button
                              variant="ghost"
                              size="sm"
                              title="Supprimer le compte"
                              disabled={self || deleteMutation.isPending}
                              onClick={() => handleDelete(user.id, user.email)}
                              className="hover:bg-red-50 hover:text-red-600"
                            >
                              <Trash2 className="h-4 w-4" />
                            </Button>
                          </div>
                        </td>
                      </tr>
                    );
                  })}
                  {data.member.length === 0 && (
                    <tr>
                      <td colSpan={4} className="px-4 py-10 text-center text-slate-500">
                        Aucun utilisateur ne correspond à cette recherche.
                      </td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>

            <div className="flex items-center justify-between border-t border-slate-200 px-4 py-3">
              <p className="text-xs text-slate-500">
                Page {page} sur {totalPages}
              </p>
              <div className="flex items-center gap-2">
                <Button variant="secondary" size="sm" disabled={page <= 1} onClick={() => setPage((p) => Math.max(1, p - 1))}>
                  Précédent
                </Button>
                <Button
                  variant="secondary"
                  size="sm"
                  disabled={page >= totalPages}
                  onClick={() => setPage((p) => Math.min(totalPages, p + 1))}
                >
                  Suivant
                </Button>
                <Button
                  variant="ghost"
                  size="sm"
                  title="Actualiser"
                  onClick={() => void queryClient.invalidateQueries({ queryKey: ['users'] })}
                >
                  <RefreshCw className="h-4 w-4" />
                </Button>
              </div>
            </div>
          </div>
        </>
      )}
    </div>
  );
}