import React, { useMemo, useState } from 'react';
import { Link, router, useForm } from '@inertiajs/react';
import { jsPDF } from 'jspdf';
import autoTable from 'jspdf-autotable';
import axios from 'axios';

export default function Index({ auth, counties = [], towns = [] }) {
    const { post } = useForm();
    const [countyQuery, setCountyQuery] = useState('');
    const [townQuery, setTownQuery] = useState('');
    const [countyName, setCountyName] = useState('');
    const [emailExportModal, setEmailExportModal] = useState({ show: false, type: '', format: '' });
    const [emailAddress, setEmailAddress] = useState('');
    const [exportLoading, setExportLoading] = useState(false);
    const [showCounties, setShowCounties] = useState(true);
    const [showTowns, setShowTowns] = useState(true);
    const [townForm, setTownForm] = useState({
        name: '',
        zip_code: '',
        county_id: '',
    });

    const isLoggedIn = Boolean(auth?.user);

    const filteredCounties = useMemo(() => {
        const query = countyQuery.trim().toLowerCase();

        if (!query) {
            return counties;
        }

        return counties.filter((county) => county.name.toLowerCase().includes(query));
    }, [counties, countyQuery]);

    const filteredTowns = useMemo(() => {
        const query = townQuery.trim().toLowerCase();

        if (!query) {
            return towns;
        }

        return towns.filter(
            (town) =>
                town.name.toLowerCase().includes(query) ||
                String(town.zip_code).toLowerCase().includes(query) ||
                String(town.county?.name ?? '').toLowerCase().includes(query),
        );
    }, [towns, townQuery]);

    const exportPDF = (rows, filename, columns) => {
        if (!rows?.length) {
            return;
        }

        const doc = new jsPDF();
        const data = rows.map((row) => columns.map((column) => row[column.key] ?? ''));

        doc.setFontSize(16);
        doc.text('Export', 14, 20);

        autoTable(doc, {
            head: [columns.map((column) => column.label)],
            body: data,
            startY: 30,
            styles: { fontSize: 10 },
            headStyles: { fillColor: [41, 128, 185] },
        });

        doc.save(filename);
    };

    const exportCSV = (rows, filename, columns, delimiter = ';') => {
        if (!rows?.length) {
            return;
        }

        const quote = (value) => {
            const text = value === null || value === undefined ? '' : String(value);
            return `"${text.replace(/"/g, '""')}"`;
        };

        const header = columns.map((column) => quote(column.label)).join(delimiter);
        const lines = rows.map((row) => columns.map((column) => quote(row[column.key])).join(delimiter));

        const csvContent = `\uFEFF${[header, ...lines].join('\n')}`;
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

    const sendEmailExport = async () => {
        if (!emailAddress.trim()) {
            alert('Please enter an email address');
            return;
        }

        setExportLoading(true);

        try {
            const { type, format } = emailExportModal;
            const query = type === 'counties' ? countyQuery : townQuery;
            
            const response = await axios.post(`/api/export/${type}/${format}`, {
                email: emailAddress.trim(),
                query: query || null,
            });

            alert(response.data.message || 'Export sent successfully!');
            setEmailExportModal({ show: false, type: '', format: '' });
            setEmailAddress('');
        } catch (error) {
            const errorMsg = error.response?.data?.error || error.message || 'Failed to send export';
            alert('Error: ' + errorMsg);
        } finally {
            setExportLoading(false);
        }
    };

    const handleLogout = () => {
        post('/logout');
    };

    const createCounty = () => {
        if (!countyName.trim()) {
            return;
        }

        router.post(
            '/counties',
            { name: countyName.trim() },
            {
                preserveScroll: true,
                onSuccess: () => setCountyName(''),
            },
        );
    };

    const editCounty = (county) => {
        const newName = prompt('Új megye név:', county.name);

        if (!newName?.trim()) {
            return;
        }

        router.put(
            `/counties/${county.id}`,
            { name: newName.trim() },
            {
                preserveScroll: true,
            },
        );
    };

    const deleteCounty = (id) => {
        if (!window.confirm('Biztosan törölni szeretnéd ezt a megyét?')) {
            return;
        }

        router.delete(`/counties/${id}`, {
            preserveScroll: true,
        });
    };

    const createTown = () => {
        if (!townForm.name.trim() || !townForm.zip_code.trim() || !townForm.county_id) {
            return;
        }

        router.post(
            '/towns',
            {
                name: townForm.name.trim(),
                zip_code: townForm.zip_code.trim(),
                county_id: Number(townForm.county_id),
            },
            {
                preserveScroll: true,
                onSuccess: () =>
                    setTownForm({
                        name: '',
                        zip_code: '',
                        county_id: '',
                    }),
            },
        );
    };

    const editTown = (town) => {
        const newName = prompt('Új település név:', town.name);

        if (!newName?.trim()) {
            return;
        }

        const newZip = prompt('Új irányítószám:', String(town.zip_code));

        if (!newZip?.trim()) {
            return;
        }

        const newCountyId = prompt('Új megye ID:', String(town.county_id));

        if (!newCountyId?.trim()) {
            return;
        }

        router.put(
            `/towns/${town.id}`,
            {
                name: newName.trim(),
                zip_code: newZip.trim(),
                county_id: Number(newCountyId),
            },
            {
                preserveScroll: true,
            },
        );
    };

    const deleteTown = (id) => {
        if (!window.confirm('Biztosan törölni szeretnéd ezt a települést?')) {
            return;
        }

        router.delete(`/towns/${id}`, {
            preserveScroll: true,
        });
    };

    const countyExportColumns = [
        { key: 'id', label: 'ID' },
        { key: 'name', label: 'Név' },
    ];

    const townExportRows = filteredTowns.map((town) => ({
        ...town,
        county_name: town.county?.name ?? '',
    }));

    const townExportColumns = [
        { key: 'id', label: 'ID' },
        { key: 'zip_code', label: 'Irányítószám' },
        { key: 'county_name', label: 'Megye' },
        { key: 'name', label: 'Név' },
    ];

    return (
        <div className="p-6">
            <h1 className="text-2xl font-bold mb-4">Irányítószám nyilvántartó</h1>

            <div className="mb-6 flex gap-3">
                {isLoggedIn ? (
                    <button onClick={handleLogout} className="bg-red-500 text-white px-4 py-2 rounded">
                        Kilépés ({auth.user.name})
                    </button>
                ) : (
                    <>
                        <Link href="/login" className="bg-blue-500 text-white px-4 py-2 rounded">
                            Belépés
                        </Link>
                        <Link href="/register" className="bg-gray-700 text-white px-4 py-2 rounded">
                            Regisztráció
                        </Link>
                    </>
                )}
            </div>

            <button
                type="button"
                onClick={() => setShowCounties((prev) => !prev)}
                className="text-xl font-semibold mt-8 mb-2 flex items-center gap-2"
            >
                <span>{showCounties ? '▼' : '▶'}</span>
                <span>Megyék</span>
            </button>
            {showCounties && (
            <>
            <div className="flex items-center gap-2 mb-3">
                <input
                    type="text"
                    placeholder="Keresés..."
                    value={countyQuery}
                    onChange={(event) => setCountyQuery(event.target.value)}
                    className="border px-2 py-1 rounded"
                />
                <button
                    onClick={() => exportCSV(filteredCounties, 'counties.csv', countyExportColumns)}
                    className="bg-green-500 text-white px-3 py-1 rounded"
                    disabled={filteredCounties.length === 0}
                >
                    Export CSV
                </button>
                <button
                    onClick={() => exportPDF(filteredCounties, 'counties.pdf', countyExportColumns)}
                    className="bg-purple-500 text-white px-3 py-1 rounded"
                    disabled={filteredCounties.length === 0}
                >
                    Export PDF
                </button>
                <button
                    onClick={() => setEmailExportModal({ show: true, type: 'counties', format: 'csv' })}
                    className="bg-blue-500 text-white px-3 py-1 rounded"
                    disabled={filteredCounties.length === 0}
                >
                    📧 Email CSV
                </button>
                <button
                    onClick={() => setEmailExportModal({ show: true, type: 'counties', format: 'pdf' })}
                    className="bg-indigo-500 text-white px-3 py-1 rounded"
                    disabled={filteredCounties.length === 0}
                >
                    📧 Email PDF
                </button>
            </div>

            {isLoggedIn && (
                <div className="flex items-center gap-2 mb-3">
                    <input
                        type="text"
                        placeholder="Új megye neve"
                        value={countyName}
                        onChange={(event) => setCountyName(event.target.value)}
                        className="border px-2 py-1 rounded"
                    />
                    <button onClick={createCounty} className="bg-blue-600 text-white px-3 py-1 rounded">
                        Megye hozzáadása
                    </button>
                </div>
            )}

            <table className="border-collapse border border-gray-300 w-full">
                <thead>
                    <tr>
                        <th className="border px-4 py-2">ID</th>
                        <th className="border px-4 py-2">Név</th>
                        {isLoggedIn && <th className="border px-4 py-2">Műveletek</th>}
                    </tr>
                </thead>
                <tbody>
                    {filteredCounties.map((county) => (
                        <tr key={county.id}>
                            <td className="border px-4 py-2">{county.id}</td>
                            <td className="border px-4 py-2">{county.name}</td>
                            {isLoggedIn && (
                                <td className="border px-4 py-2 flex gap-2">
                                    <button
                                        onClick={() => editCounty(county)}
                                        className="bg-yellow-500 text-white px-2 py-1 rounded"
                                    >
                                        Módosítás
                                    </button>
                                    <button
                                        onClick={() => deleteCounty(county.id)}
                                        className="bg-red-500 text-white px-2 py-1 rounded"
                                    >
                                        Törlés
                                    </button>
                                </td>
                            )}
                        </tr>
                    ))}
                </tbody>
            </table>
            </>
            )}

            <button
                type="button"
                onClick={() => setShowTowns((prev) => !prev)}
                className="text-xl font-semibold mt-8 mb-2 flex items-center gap-2"
            >
                <span>{showTowns ? '▼' : '▶'}</span>
                <span>Települések</span>
            </button>
            {showTowns && (
            <>
            <div className="flex items-center gap-2 mb-3">
                <input
                    type="text"
                    placeholder="Keresés név / irányítószám / megye..."
                    value={townQuery}
                    onChange={(event) => setTownQuery(event.target.value)}
                    className="border px-2 py-1 rounded w-80"
                />
                <button
                    onClick={() => exportCSV(townExportRows, 'towns.csv', townExportColumns)}
                    className="bg-green-500 text-white px-3 py-1 rounded"
                    disabled={townExportRows.length === 0}
                >
                    Export CSV
                </button>
                <button
                    onClick={() => exportPDF(townExportRows, 'towns.pdf', townExportColumns)}
                    className="bg-purple-500 text-white px-3 py-1 rounded"
                    disabled={townExportRows.length === 0}
                >
                    Export PDF
                </button>
                <button
                    onClick={() => setEmailExportModal({ show: true, type: 'towns', format: 'csv' })}
                    className="bg-blue-500 text-white px-3 py-1 rounded"
                    disabled={townExportRows.length === 0}
                >
                    📧 Email CSV
                </button>
                <button
                    onClick={() => setEmailExportModal({ show: true, type: 'towns', format: 'pdf' })}
                    className="bg-indigo-500 text-white px-3 py-1 rounded"
                    disabled={townExportRows.length === 0}
                >
                    📧 Email PDF
                </button>
            </div>

            {isLoggedIn && (
                <div className="flex items-center gap-2 mb-3 flex-wrap">
                    <input
                        type="text"
                        placeholder="Település neve"
                        value={townForm.name}
                        onChange={(event) => setTownForm((prev) => ({ ...prev, name: event.target.value }))}
                        className="border px-2 py-1 rounded"
                    />
                    <input
                        type="text"
                        placeholder="Irányítószám"
                        value={townForm.zip_code}
                        onChange={(event) => setTownForm((prev) => ({ ...prev, zip_code: event.target.value }))}
                        className="border px-2 py-1 rounded"
                    />
                    <select
                        value={townForm.county_id}
                        onChange={(event) => setTownForm((prev) => ({ ...prev, county_id: event.target.value }))}
                        className="border px-2 py-1 rounded"
                    >
                        <option value="">Válassz megyét</option>
                        {counties.map((county) => (
                            <option key={county.id} value={county.id}>
                                {county.name}
                            </option>
                        ))}
                    </select>
                    <button onClick={createTown} className="bg-blue-600 text-white px-3 py-1 rounded">
                        Település hozzáadása
                    </button>
                </div>
            )}

            <table className="border-collapse border border-gray-300 w-full">
                <thead>
                    <tr>
                        <th className="border px-4 py-2">ID</th>
                        <th className="border px-4 py-2">Irányítószám</th>
                        <th className="border px-4 py-2">Megye</th>
                        <th className="border px-4 py-2">Név</th>
                        {isLoggedIn && <th className="border px-4 py-2">Műveletek</th>}
                    </tr>
                </thead>
                <tbody>
                    {filteredTowns.map((town) => (
                        <tr key={town.id}>
                            <td className="border px-4 py-2">{town.id}</td>
                            <td className="border px-4 py-2">{town.zip_code}</td>
                            <td className="border px-4 py-2">{town.county?.name ?? town.county_id}</td>
                            <td className="border px-4 py-2">{town.name}</td>
                            {isLoggedIn && (
                                <td className="border px-4 py-2 flex gap-2">
                                    <button
                                        onClick={() => editTown(town)}
                                        className="bg-yellow-500 text-white px-2 py-1 rounded"
                                    >
                                        Módosítás
                                    </button>
                                    <button
                                        onClick={() => deleteTown(town.id)}
                                        className="bg-red-500 text-white px-2 py-1 rounded"
                                    >
                                        Törlés
                                    </button>
                                </td>
                            )}
                        </tr>
                    ))}
                </tbody>
            </table>
            </>
            )}

            {/* Email Export Modal */}
            {emailExportModal.show && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div className="bg-white rounded-lg p-6 w-96 shadow-xl">
                        <h3 className="text-lg font-semibold mb-4">
                            Email {emailExportModal.type === 'counties' ? 'Counties' : 'Towns'} Export ({emailExportModal.format.toUpperCase()})
                        </h3>
                        <p className="text-sm text-gray-600 mb-4">
                            Enter the email address where you want to receive the export file.
                        </p>
                        <input
                            type="email"
                            placeholder="email@example.com"
                            value={emailAddress}
                            onChange={(e) => setEmailAddress(e.target.value)}
                            className="border px-3 py-2 rounded w-full mb-4"
                            disabled={exportLoading}
                        />
                        <div className="flex gap-2 justify-end">
                            <button
                                onClick={() => {
                                    setEmailExportModal({ show: false, type: '', format: '' });
                                    setEmailAddress('');
                                }}
                                className="bg-gray-400 text-white px-4 py-2 rounded"
                                disabled={exportLoading}
                            >
                                Cancel
                            </button>
                            <button
                                onClick={sendEmailExport}
                                className="bg-blue-600 text-white px-4 py-2 rounded disabled:bg-blue-300"
                                disabled={exportLoading || !emailAddress.trim()}
                            >
                                {exportLoading ? 'Sending...' : 'Send Email'}
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
