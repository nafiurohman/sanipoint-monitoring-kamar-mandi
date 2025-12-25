import React, { useState, useEffect } from 'react';
import Input from '../../ui/Input/Input';
import Button from '../../ui/Button/Button';

const ProductForm = ({ 
  product = null, 
  onSubmit, 
  onCancel, 
  loading = false 
}) => {
  const [formData, setFormData] = useState({
    barcode: '',
    nama_produk: '',
    kategori: '',
    harga_beli: '',
    harga_jual: '',
    stok: '',
    stok_minimum: '5',
    satuan: 'pcs',
    supplier: '',
    deskripsi: ''
  });

  const [errors, setErrors] = useState({});

  useEffect(() => {
    if (product) {
      setFormData({
        barcode: product.barcode || '',
        nama_produk: product.nama_produk || '',
        kategori: product.kategori || '',
        harga_beli: product.harga_beli || '',
        harga_jual: product.harga_jual || '',
        stok: product.stok || '',
        stok_minimum: product.stok_minimum || '5',
        satuan: product.satuan || 'pcs',
        supplier: product.supplier || '',
        deskripsi: product.deskripsi || ''
      });
    }
  }, [product]);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
    
    // Clear error when user starts typing
    if (errors[name]) {
      setErrors(prev => ({
        ...prev,
        [name]: ''
      }));
    }
  };

  const validateForm = () => {
    const newErrors = {};

    if (!formData.nama_produk.trim()) {
      newErrors.nama_produk = 'Nama produk wajib diisi';
    }

    if (!formData.harga_beli || parseFloat(formData.harga_beli) <= 0) {
      newErrors.harga_beli = 'Harga beli harus lebih dari 0';
    }

    if (!formData.harga_jual || parseFloat(formData.harga_jual) <= 0) {
      newErrors.harga_jual = 'Harga jual harus lebih dari 0';
    }

    if (parseFloat(formData.harga_jual) <= parseFloat(formData.harga_beli)) {
      newErrors.harga_jual = 'Harga jual harus lebih besar dari harga beli';
    }

    if (formData.stok === '' || parseInt(formData.stok) < 0) {
      newErrors.stok = 'Stok tidak boleh negatif';
    }

    if (!formData.stok_minimum || parseInt(formData.stok_minimum) < 0) {
      newErrors.stok_minimum = 'Stok minimum tidak boleh negatif';
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    
    if (validateForm()) {
      onSubmit({
        ...formData,
        harga_beli: parseFloat(formData.harga_beli),
        harga_jual: parseFloat(formData.harga_jual),
        stok: parseInt(formData.stok) || 0,
        stok_minimum: parseInt(formData.stok_minimum) || 5
      });
    }
  };

  const calculateProfit = () => {
    const hargaBeli = parseFloat(formData.harga_beli) || 0;
    const hargaJual = parseFloat(formData.harga_jual) || 0;
    
    if (hargaBeli > 0 && hargaJual > 0) {
      const profit = hargaJual - hargaBeli;
      const margin = ((profit / hargaBeli) * 100).toFixed(1);
      return { profit, margin };
    }
    return { profit: 0, margin: 0 };
  };

  const { profit, margin } = calculateProfit();

  return (
    <form onSubmit={handleSubmit} className="space-y-6">
      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        {/* Basic Information */}
        <div className="space-y-4">
          <h3 className="text-lg font-medium text-light-text dark:text-dark-text">
            Informasi Dasar
          </h3>
          
          <Input
            label="Barcode"
            name="barcode"
            value={formData.barcode}
            onChange={handleChange}
            placeholder="Kode barcode (opsional)"
            error={errors.barcode}
          />

          <Input
            label="Nama Produk *"
            name="nama_produk"
            value={formData.nama_produk}
            onChange={handleChange}
            placeholder="Masukkan nama produk"
            error={errors.nama_produk}
            required
          />

          <Input
            label="Kategori"
            name="kategori"
            value={formData.kategori}
            onChange={handleChange}
            placeholder="Kategori produk"
            error={errors.kategori}
          />

          <Input
            label="Deskripsi"
            name="deskripsi"
            value={formData.deskripsi}
            onChange={handleChange}
            placeholder="Deskripsi produk (opsional)"
            as="textarea"
            rows={3}
          />
        </div>

        {/* Pricing & Stock */}
        <div className="space-y-4">
          <h3 className="text-lg font-medium text-light-text dark:text-dark-text">
            Harga & Stok
          </h3>

          <div className="grid grid-cols-2 gap-4">
            <Input
              label="Harga Beli *"
              name="harga_beli"
              type="number"
              value={formData.harga_beli}
              onChange={handleChange}
              placeholder="0"
              min="0"
              step="100"
              error={errors.harga_beli}
              required
            />

            <Input
              label="Harga Jual *"
              name="harga_jual"
              type="number"
              value={formData.harga_jual}
              onChange={handleChange}
              placeholder="0"
              min="0"
              step="100"
              error={errors.harga_jual}
              required
            />
          </div>

          {profit > 0 && (
            <div className="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
              <div className="flex justify-between text-sm">
                <span className="text-blue-700 dark:text-blue-300">Keuntungan:</span>
                <span className="font-medium text-blue-700 dark:text-blue-300">
                  Rp {profit.toLocaleString('id-ID')} ({margin}%)
                </span>
              </div>
            </div>
          )}

          <div className="grid grid-cols-2 gap-4">
            <Input
              label="Stok Awal"
              name="stok"
              type="number"
              value={formData.stok}
              onChange={handleChange}
              placeholder="0"
              min="0"
              error={errors.stok}
            />

            <Input
              label="Stok Minimum"
              name="stok_minimum"
              type="number"
              value={formData.stok_minimum}
              onChange={handleChange}
              placeholder="5"
              min="0"
              error={errors.stok_minimum}
            />
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium text-light-text dark:text-dark-text mb-1">
                Satuan
              </label>
              <select
                name="satuan"
                value={formData.satuan}
                onChange={handleChange}
                className="w-full px-3 py-2 border border-light-border rounded-lg focus:outline-none focus:ring-2 focus:ring-light-primary focus:border-transparent dark:bg-dark-surface dark:border-dark-border dark:text-dark-text dark:focus:ring-dark-primary"
              >
                <option value="pcs">Pcs</option>
                <option value="kg">Kg</option>
                <option value="gram">Gram</option>
                <option value="liter">Liter</option>
                <option value="pack">Pack</option>
                <option value="dus">Dus</option>
              </select>
            </div>

            <Input
              label="Supplier"
              name="supplier"
              value={formData.supplier}
              onChange={handleChange}
              placeholder="Nama supplier"
            />
          </div>
        </div>
      </div>

      {/* Form Actions */}
      <div className="flex justify-end space-x-3 pt-6 border-t border-light-border dark:border-dark-border">
        <Button
          type="button"
          variant="secondary"
          onClick={onCancel}
          disabled={loading}
        >
          Batal
        </Button>
        
        <Button
          type="submit"
          loading={loading}
        >
          {product ? 'Update Produk' : 'Tambah Produk'}
        </Button>
      </div>
    </form>
  );
};

export default ProductForm;