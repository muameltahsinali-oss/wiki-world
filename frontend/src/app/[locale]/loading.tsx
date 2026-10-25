export default function LoadingPage() {
  return (
    <main className="mx-auto flex min-h-screen items-center justify-center">
      <div
        className="h-8 w-8 animate-spin rounded-full border-2 border-[var(--color-border-default)] border-t-[var(--color-scholar)]"
        role="status"
        aria-label="Loading"
      />
    </main>
  );
}
