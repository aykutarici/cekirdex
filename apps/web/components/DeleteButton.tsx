'use client';

export function DeleteButton({
  action,
  confirmMessage,
  label = 'Sil',
  className,
}: {
  action: (fd: FormData) => Promise<void>;
  confirmMessage: string;
  label?: string;
  className?: string;
}) {
  return (
    <form
      action={action}
      onSubmit={(e) => {
        if (!confirm(confirmMessage)) e.preventDefault();
      }}
    >
      <button type="submit" className={className ?? 'text-xs text-red-500 hover:text-red-700'}>
        {label}
      </button>
    </form>
  );
}
