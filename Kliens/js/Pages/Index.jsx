import React, { useEffect, useState } from 'react';
import { Link, useForm } from '@inertiajs/react';

export default function Index({ auth }) {
    const { post } = useForm();
    const [towns, setTowns] = useState([]);
    const [counties, setCounties] = useState([]);
    const [countyQuery, setCountyQuery] = useState('');
    const [townQuery, setTownQuery] = useState('');
    const token = localStorage.getItem('token');
    console.log(auth)
    useEffect(() => {
        fetch('/api/towns')
            .then(res => res.json())
            .then(json => setTowns(json.towns || []))
            .catch(err => console.error(err));

        fetch('/api/counties')
            .then(res => res.json())
            .then(json => setCounties(json.counties || []))
            .catch(err => console.error(err));
    }, []);

    const handleLogout = () => {
        post('/logout');
    };

    const exportCSV = (rows, filename, delimiter = ';') => {
        if (!rows || rows.length === 0) return;

        const headers = ['id', 'name'];
        const quote = (val) => {
            const s = val === null || val === undefined ? '' : String(val);
            const escaped = s.replace(/"/g, '""');
            return `"${escaped}"`;
        };

        const lines = [];
        lines.push(headers.map(h => quote(h)).join(delimiter));
        rows.forEach(r => {
            const id = r.id ?? '';
            const name = r.name ?? '';
            lines.push([quote(id), quote(name)].join(delimiter));
        });

        const bom = '\uFEFF';
        const csvContent = bom + lines.join('\n');

        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    };
    const handleEditCounty = async (county) => {
        const newName = prompt("Új név:", county.name);
        if (!newName) return;
      
        try {
          const res = await fetch(`http://localhost:8000/api/counties/${county.id}`, {
            method: "PUT",
            headers: {
              "Authorization": `Bearer ${token}`,
              "Content-Type": "application/json",
              "Accept": "application/json"
            },
            body: JSON.stringify({ name: newName })
          });
          const data = await res.json();
          setCounties(counties.map(c => c.id === county.id ? data.county : c));
        } catch (err) {
          console.error(err);
        }
      };
      
      const handleDeleteCounty = async (id) => {
        if (!window.confirm("Biztosan ki akarja törölni ezt a megyét?")) return;
      
        try {
          await fetch(`http://localhost:8000/api/counties/${id}`, {
            method: "DELETE",
            headers: {
              "Authorization": `Bearer ${token}`,
              "Accept": "application/json"
            }
          });
          setCounties(counties.filter(c => c.id !== id));
        } catch (err) {
          console.error(err);
        }
      };

      const handleEditTown = async (county) => {
        const newName = prompt("Új név:", county.name);
        if (!newName) return;
      
        try {
          const res = await fetch(`http://localhost:8000/api/towns/${county.id}`, {
            method: "PUT",
            headers: {
              "Authorization": `Bearer ${token}`,
              "Content-Type": "application/json",
              "Accept": "application/json"
            },
            body: JSON.stringify({ name: newName })
          });
          const data = await res.json();
          setCounties(counties.map(c => c.id === county.id ? data.county : c));
        } catch (err) {
          console.error(err);
        }
      };
      
      const handleDeleteTown = async (id) => {
        if (!window.confirm("Biztosan ki akarja törölni ezt a települést?")) return;
      
        try {
          await fetch(`http://localhost:8000/api/towns/${id}`, {
            method: "DELETE",
            headers: {
              "Authorization": `Bearer ${token}`,
              "Accept": "application/json"
            }
          });
          setCounties(counties.filter(c => c.id !== id));
        } catch (err) {
          console.error(err);
        }
      };
      

    const searchCounties = () => {
        if (!countyQuery.trim()) return;
        console.log('token', token);

        fetch(`/api/counties/${encodeURIComponent(countyQuery)}`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        })
            .then(res => res.json())
            .then(json => setCounties(json.county || json.counties || []))
            .catch(err => console.error(err));
    };
    
    const searchTowns = () => {
        if (!townQuery.trim()) return;
        fetch(`/api/towns/${encodeURIComponent(townQuery)}`, {
            headers: {
                'Authorization': `Bearer ${token}`,

                'Accept': 'application/json'
            }
        })
            .then(res => res.json())
            .then(json => setTowns(json.town || json.towns || []))
            .catch(err => console.error(err));
    };
    

    return (
        <div className="p-6">
            <h1 className="text-2xl font-bold mb-4">Homepage</h1>

            {JSON.parse(localStorage.user).name ? (
                <button
                    onClick={handleLogout}
                    className="bg-red-500 text-white px-4 py-2 rounded"
                >
                    Kilépés ({JSON.parse(localStorage.user).name})
                </button>
            ) : (
                <Link
                    href="/login"
                    className="bg-blue-500 text-white px-4 py-2 rounded"
                >
                    Belépés
                </Link>
            )}

            {/* Counties Search */}
            <h2 className="text-xl font-semibold mt-8 mb-2">Megyék</h2>
            <div className="flex items-center gap-2 mb-2">
                <input
                    type="text"
                    placeholder="Keresés..."
                    value={countyQuery}
                    onChange={e => setCountyQuery(e.target.value)}
                    className="border px-2 py-1 rounded"
                />
                <button
                    onClick={searchCounties}
                    className="bg-blue-500 text-white px-3 py-1 rounded"
                >
                    Keresés
                </button>
                <button
                    onClick={() => exportCSV(counties, 'counties.csv')}
                    className="bg-green-500 text-white px-3 py-1 rounded"
                    disabled={counties.length === 0}
                >
                    Export CSV
                </button>
            </div>
            <table className="border-collapse border border-gray-300 w-full">
  <thead>
    <tr>
      <th className="border px-4 py-2">ID</th>
      <th className="border px-4 py-2">Név</th>
      <th className="border px-4 py-2">Műveletek</th>
    </tr>
  </thead>
  <tbody>
    {counties.map((county) => (
      <tr key={county.id}>
        <td className="border px-4 py-2">{county.id}</td>
        <td className="border px-4 py-2">{county.name}</td>
        <td className="border px-4 py-2 flex gap-2">
          <button
            onClick={() => handleEditCounty(county)}
            className="bg-yellow-500 text-white px-2 py-1 rounded"
          >
            Módosítás
          </button>
          <button
            onClick={() => handleDeleteCounty(county.id)}
            className="bg-red-500 text-white px-2 py-1 rounded"
          >
            Törlés
          </button>
        </td>
      </tr>
    ))}
  </tbody>
</table>


            {/* Towns Search */}
            <h2 className="text-xl font-semibold mt-8 mb-2">Towns</h2>
            <div className="flex items-center gap-2 mb-2">
                <input
                    type="text"
                    placeholder="Keresés..."
                    value={townQuery}
                    onChange={e => setTownQuery(e.target.value)}
                    className="border px-2 py-1 rounded"
                />
                <button
                    onClick={searchTowns}
                    className="bg-blue-500 text-white px-3 py-1 rounded"
                >
                    Keresés
                </button>
                <button
                    onClick={() => exportCSV(towns, 'towns.csv')}
                    className="bg-green-500 text-white px-3 py-1 rounded"
                    disabled={towns.length === 0}
                >
                    Export CSV
                </button>
            </div>
            <table className="border-collapse border border-gray-300 w-full">
  <thead>
    <tr>
    <th className="border px-4 py-2">ID</th>
        <th className="border px-4 py-2">Irányítószám</th>
        <th className="border px-4 py-2">Megye ID</th>
      <th className="border px-4 py-2">Név</th>
      <th className="border px-4 py-2">Műveletek</th>
    </tr>
  </thead>
  <tbody>
    {towns.map((town) => (
      <tr key={town.id}>
        <td className="border px-4 py-2">{town.id}</td>
        <td className="border px-4 py-2">{town.zip_code}</td>
        <td className="border px-4 py-2">{town.county_id}</td>
        <td className="border px-4 py-2">{town.name}</td>
        <td className="border px-4 py-2 flex gap-2">
          <button
            onClick={() => handleEditTown(town)}
            className="bg-yellow-500 text-white px-2 py-1 rounded"
          >
            Módosítás
          </button>
          <button
            onClick={() => handleDeleteTown(town.id)}
            className="bg-red-500 text-white px-2 py-1 rounded"
          >
            Törlés
          </button>
        </td>
      </tr>
    ))}
  </tbody>
</table>

        </div>
    );
}