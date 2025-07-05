import { ref } from "vue";

interface User {
  pseudo: string | null;
  image: string | null;
}

const user = ref<User | null>(null);
const error = ref('');

function getJwtFromCookie(): string | null {
  const match = document.cookie.match(/(?:^| )jwt=([^;]+)/);
  return match ? match[1] : null;
}
function getCookie(name: string): string | null {
        const match = document.cookie.match(new RegExp(`(?:^| )${name}=([^;]+)`));
        return match ? decodeURIComponent(match[1]) : null;
        }
export async function  getSpeudoImage() {
    if(getJwtFromCookie()) {
        try {
            const token = getJwtFromCookie();

            if (!token) throw new Error('Aucun token trouvé');

            const res = await fetch('http://localhost:8000/api/me', {
            headers: {
                Authorization: `Bearer ${token}`,
                Accept: 'application/ld+json',
            },
            });

            if (!res.ok) throw new Error('Erreur API');

            const userData = await res.json() as User;
            user.value = userData;

            // Vérifie si les cookies existent déjà
            const pseudoCookie = getCookie('pseudo');
            const imageCookie = getCookie('image');

            // S'ils n'existent pas, on les crée pour 1h maxAge = 3600s

            if (pseudoCookie != userData.pseudo && userData.pseudo) {
                document.cookie = `pseudo=${encodeURIComponent(userData.pseudo)}; path=/; max-age=432000; secure; samesite=Strict`;
            }
            
            console.log(imageCookie, userData.image);
            if (imageCookie != userData.image && userData.image) {
                document.cookie = `image=${encodeURIComponent(userData.image)}; path=/; max-age=432000; secure; samesite=Strict`;
            }
            

        } catch (e) {
            console.error(e);
            error.value = 'Erreur lors du chargement du profil';
        }
    }
}