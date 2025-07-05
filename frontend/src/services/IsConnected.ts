
export function getJwtFromCookie(): string | null {
  const match = document.cookie.match(new RegExp('(^| )jwt=([^;]+)'));
  return match ? match[2] : null;
}

export function getCookie(name: string): string | null {
  const match = document.cookie.match(new RegExp(`(?:^| )${name}=([^;]+)`));
  return match ? decodeURIComponent(match[1]) : null;
}