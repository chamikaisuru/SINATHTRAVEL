import { useState } from "react";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { 
  getAdminPackages, 
  createAdminPackage,
  updateAdminPackage,
  deleteAdminPackage,
  type AdminPackage 
} from "@/lib/adminApi";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from "@/components/ui/card";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog";
import { Label } from "@/components/ui/label";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { useToast } from "@/hooks/use-toast";
import { Plus, Edit, Trash2, Loader2, Package, AlertCircle, Star, Gift, Sparkles } from "lucide-react";
import { Badge } from "@/components/ui/badge";
import { Alert, AlertDescription } from "@/components/ui/alert";

export default function AdminPackages() {
  const { toast } = useToast();
  const queryClient = useQueryClient();
  const [dialogOpen, setDialogOpen] = useState(false);
  const [editingPackage, setEditingPackage] = useState<AdminPackage | null>(null);
  const [selectedImage, setSelectedImage] = useState<File | null>(null);
  const [imagePreview, setImagePreview] = useState<string | null>(null);

  // ⭐ UPDATED: Form state with featured_type
  const [formData, setFormData] = useState({
    category: 'tour',
    title_en: '',
    description_en: '',
    price: '',
    duration: '',
    status: 'active',
    featured_type: '' // ⭐ ADDED
  });

  const { data: packages = [], isLoading, error } = useQuery<AdminPackage[]>({
    queryKey: ['admin-packages'],
    queryFn: async () => {
      console.log('🔍 Fetching packages from API...');
      const result = await getAdminPackages();
      console.log('🔍 API returned:', result);
      
      if (Array.isArray(result)) {
        console.log('✅ Returning', result.length, 'packages');
        return result;
      }
      
      console.error('❌ Unexpected response format:', result);
      return [];
    },
  });

  const createMutation = useMutation({
    mutationFn: createAdminPackage,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['admin-packages'] });
      toast({ title: "✅ Package created successfully" });
      handleCloseDialog();
    },
    onError: (error: Error) => {
      toast({ 
        title: "❌ Failed to create package", 
        description: error.message, 
        variant: "destructive" 
      });
    },
  });

  const updateMutation = useMutation({
    mutationFn: ({ id, data }: { id: number; data: Partial<AdminPackage> }) =>
      updateAdminPackage(id, data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['admin-packages'] });
      toast({ title: "✅ Package updated successfully" });
      handleCloseDialog();
    },
    onError: (error: Error) => {
      toast({ 
        title: "❌ Failed to update package", 
        description: error.message, 
        variant: "destructive" 
      });
    },
  });

  const deleteMutation = useMutation({
    mutationFn: deleteAdminPackage,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['admin-packages'] });
      toast({ title: "✅ Package deleted successfully" });
    },
    onError: (error: Error) => {
      toast({ 
        title: "❌ Failed to delete package", 
        description: error.message, 
        variant: "destructive" 
      });
    },
  });

  // ⭐ UPDATED: handleOpenDialog with featured_type
  function handleOpenDialog(pkg?: AdminPackage) {
    if (pkg) {
      setEditingPackage(pkg);
      setFormData({
        category: pkg.category,
        title_en: pkg.title_en,
        description_en: pkg.description_en,
        price: pkg.price.toString(),
        duration: pkg.duration || '',
        status: pkg.status,
        featured_type: pkg.featured_type || '', // ⭐ ADDED
      });
      if (pkg.image) {
        setImagePreview(pkg.image);
      }
    } else {
      setEditingPackage(null);
      setFormData({
        category: 'tour',
        title_en: '',
        description_en: '',
        price: '',
        duration: '',
        status: 'active',
        featured_type: '' // ⭐ ADDED
      });
      setImagePreview(null);
    }
    setDialogOpen(true);
  }

  function handleCloseDialog() {
    setDialogOpen(false);
    setEditingPackage(null);
    setSelectedImage(null);
    setImagePreview(null);
    setFormData({
      category: 'tour',
      title_en: '',
      description_en: '',
      price: '',
      duration: '',
      status: 'active',
      featured_type: '' // ⭐ ADDED
    });
  }

  function handleImageChange(e: React.ChangeEvent<HTMLInputElement>) {
    const file = e.target.files?.[0];
    if (file) {
      setSelectedImage(file);
      const reader = new FileReader();
      reader.onloadend = () => {
        setImagePreview(reader.result as string);
      };
      reader.readAsDataURL(file);
    }
  }

  function handleSubmit(e: React.FormEvent) {
    e.preventDefault();

    // Validate
    if (!formData.title_en || !formData.description_en || !formData.price) {
      toast({
        title: "❌ Validation Error",
        description: "Please fill in all required fields",
        variant: "destructive"
      });
      return;
    }

    if (editingPackage?.id) {
      // UPDATING EXISTING PACKAGE
      console.log('📝 Updating package:', editingPackage.id);
      console.log('📝 Has new image:', !!selectedImage);
      
      if (selectedImage) {
        // Has new image - use FormData
        const submitFormData = new FormData();
        
        // Add all fields
        Object.entries(formData).forEach(([key, value]) => {
          if (value) submitFormData.append(key, value.toString());
        });
        
        // Add image file
        submitFormData.append('image', selectedImage);
        
        console.log('📝 Sending FormData with new image');
        
        updateMutation.mutate({ 
          id: editingPackage.id, 
          data: submitFormData 
        });
      } else {
        // No new image - send as object
        console.log('📝 Sending data without image change');
        
        updateMutation.mutate({ 
          id: editingPackage.id, 
          data: {
            ...formData,
            price: parseFloat(formData.price),
          }
        });
      }
    } else {
      // CREATING NEW PACKAGE
      console.log('📝 Creating new package');
      
      const submitFormData = new FormData();
      Object.entries(formData).forEach(([key, value]) => {
        if (value) submitFormData.append(key, value.toString());
      });
      
      if (selectedImage) {
        submitFormData.append('image', selectedImage);
        console.log('📝 Added image to FormData');
      }
      
      createMutation.mutate(submitFormData);
    }
  }

  function handleDelete(id: number) {
    if (confirm('Are you sure you want to delete this package? This action cannot be undone.')) {
      deleteMutation.mutate(id);
    }
  }

  if (isLoading) {
    return (
      <div className="space-y-6">
        <h1 className="text-3xl font-heading font-bold text-primary">Packages</h1>
        <div className="flex items-center justify-center py-12">
          <Loader2 className="h-12 w-12 animate-spin text-primary" />
          <span className="ml-4 text-muted-foreground">Loading packages...</span>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="space-y-6">
        <h1 className="text-3xl font-heading font-bold text-primary">Packages</h1>
        <Alert variant="destructive">
          <AlertCircle className="h-4 w-4" />
          <AlertDescription>
            Error loading packages: {error.toString()}
            <Button 
              onClick={() => queryClient.invalidateQueries({ queryKey: ['admin-packages'] })} 
              variant="outline" 
              size="sm" 
              className="ml-4"
            >
              Retry
            </Button>
          </AlertDescription>
        </Alert>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-3xl font-heading font-bold text-primary">
          Packages ({packages.length})
        </h1>
        <Dialog open={dialogOpen} onOpenChange={setDialogOpen}>
          <DialogTrigger asChild>
            <Button 
              className="bg-secondary hover:bg-secondary/90"
              onClick={() => handleOpenDialog()}
            >
              <Plus className="w-4 h-4 mr-2" />
              Add Package
            </Button>
          </DialogTrigger>
          <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
            <DialogHeader>
              <DialogTitle>
                {editingPackage ? 'Edit Package' : 'Add New Package'}
              </DialogTitle>
            </DialogHeader>
            <form onSubmit={handleSubmit} className="space-y-4">
              <div className="grid grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label htmlFor="category">Category</Label>
                  <Select 
                    value={formData.category}
                    onValueChange={(value) => setFormData({...formData, category: value})}
                  >
                    <SelectTrigger>
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="tour">Tour Package</SelectItem>
                      <SelectItem value="visa">Visa Service</SelectItem>
                      <SelectItem value="ticket">Flight Ticket</SelectItem>
                      <SelectItem value="offer">Special Offer</SelectItem>
                    </SelectContent>
                  </Select>
                </div>

                <div className="space-y-2">
                  <Label htmlFor="status">Status</Label>
                  <Select 
                    value={formData.status}
                    onValueChange={(value) => setFormData({...formData, status: value})}
                  >
                    <SelectTrigger>
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="active">Active</SelectItem>
                      <SelectItem value="inactive">Inactive</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
              </div>

              {/* ⭐ NEW: Featured Type Dropdown */}
              <div className="space-y-2">
                <Label htmlFor="featured">Featured Type (Optional)</Label>
                <Select 
                  value={formData.featured_type}
                  onValueChange={(value) => setFormData({...formData, featured_type: value})}
                >
                  <SelectTrigger>
                    <SelectValue placeholder="Regular Package" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="">Regular Package</SelectItem>
                    <SelectItem value="popular">
                      <div className="flex items-center gap-2">
                        <Star className="w-4 h-4 fill-amber-500 text-amber-500" />
                        Popular Package
                      </div>
                    </SelectItem>
                    <SelectItem value="special_offer">
                      <div className="flex items-center gap-2">
                        <Gift className="w-4 h-4 text-red-500" />
                        Special Offer
                      </div>
                    </SelectItem>
                    <SelectItem value="seasonal">
                      <div className="flex items-center gap-2">
                        <Sparkles className="w-4 h-4 text-green-500" />
                        Seasonal Package
                      </div>
                    </SelectItem>
                  </SelectContent>
                </Select>
                <p className="text-xs text-muted-foreground">
                  Featured packages appear on the home page in special sections
                </p>
              </div>

              <div className="space-y-2">
                <Label htmlFor="title">Title (English) *</Label>
                <Input 
                  id="title"
                  value={formData.title_en}
                  onChange={(e) => setFormData({...formData, title_en: e.target.value})}
                  placeholder="e.g., Dubai Shopping Festival"
                  required
                />
              </div>

              <div className="space-y-2">
                <Label htmlFor="description">Description (English) *</Label>
                <Textarea 
                  id="description"
                  value={formData.description_en}
                  onChange={(e) => setFormData({...formData, description_en: e.target.value})}
                  placeholder="Describe the package..." 
                  rows={4}
                  required
                />
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label htmlFor="price">Price (USD) *</Label>
                  <Input 
                    id="price"
                    type="number" 
                    step="0.01"
                    value={formData.price}
                    onChange={(e) => setFormData({...formData, price: e.target.value})}
                    placeholder="500.00"
                    required
                  />
                </div>

                <div className="space-y-2">
                  <Label htmlFor="duration">Duration (Optional)</Label>
                  <Input 
                    id="duration"
                    value={formData.duration}
                    onChange={(e) => setFormData({...formData, duration: e.target.value})}
                    placeholder="e.g., 5 Days"
                  />
                </div>
              </div>

              <div className="space-y-2">
                <Label htmlFor="image">Package Image</Label>
                <Input 
                  id="image"
                  type="file" 
                  accept="image/*"
                  onChange={handleImageChange}
                />
                {imagePreview && (
                  <div className="mt-4">
                    <img 
                      src={imagePreview} 
                      alt="Preview" 
                      className="w-full h-48 object-cover rounded-lg"
                    />
                  </div>
                )}
              </div>

              <div className="flex gap-2 pt-4">
                <Button 
                  type="submit" 
                  disabled={createMutation.isPending || updateMutation.isPending}
                  className="flex-1"
                >
                  {(createMutation.isPending || updateMutation.isPending) ? (
                    <>
                      <Loader2 className="w-4 h-4 mr-2 animate-spin" />
                      {editingPackage ? 'Updating...' : 'Creating...'}
                    </>
                  ) : (
                    editingPackage ? 'Update Package' : 'Create Package'
                  )}
                </Button>
                <Button 
                  type="button" 
                  variant="outline" 
                  onClick={handleCloseDialog}
                >
                  Cancel
                </Button>
              </div>
            </form>
          </DialogContent>
        </Dialog>
      </div>

      {packages.length === 0 ? (
        <Card>
          <CardContent className="py-12 text-center">
            <Package className="w-16 h-16 mx-auto mb-4 text-muted-foreground" />
            <h3 className="text-lg font-semibold mb-2">No packages found</h3>
            <p className="text-sm text-muted-foreground mb-4">
              Click "Add Package" to create your first package
            </p>
            <Button onClick={() => handleOpenDialog()}>
              <Plus className="w-4 h-4 mr-2" />
              Add Your First Package
            </Button>
          </CardContent>
        </Card>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {/* ⭐ UPDATED: Package cards with featured badges */}
          {packages.map((pkg) => (
            <Card key={pkg.id} className="overflow-hidden hover:shadow-lg transition-shadow">
              {pkg.image && (
                <div className="h-48 overflow-hidden bg-muted relative">
                  <img 
                    src={pkg.image} 
                    alt={pkg.title_en} 
                    className="w-full h-full object-cover"
                  />
                  <Badge 
                    variant={pkg.status === 'active' ? 'default' : 'secondary'}
                    className="absolute top-2 right-2"
                  >
                    {pkg.status}
                  </Badge>
                </div>
              )}
              <CardHeader>
                <div className="flex items-start justify-between gap-2">
                  <CardTitle className="text-lg line-clamp-2">{pkg.title_en}</CardTitle>
                </div>
                
                {/* ⭐ NEW: Badges section */}
                <div className="flex gap-2 flex-wrap mt-2">
                  <Badge variant="outline" className="w-fit text-xs">
                    {pkg.category}
                  </Badge>
                  
                  {/* ⭐ NEW: Featured badge */}
                  {pkg.featured_type && (
                    <Badge 
                      className={`w-fit text-xs ${
                        pkg.featured_type === 'popular' 
                          ? 'bg-amber-100 text-amber-800 hover:bg-amber-200 dark:bg-amber-900 dark:text-amber-100' 
                          : pkg.featured_type === 'special_offer'
                          ? 'bg-red-100 text-red-800 hover:bg-red-200 dark:bg-red-900 dark:text-red-100'
                          : 'bg-green-100 text-green-800 hover:bg-green-200 dark:bg-green-900 dark:text-green-100'
                      }`}
                    >
                      {pkg.featured_type === 'popular' && (
                        <>
                          <Star className="w-3 h-3 mr-1 fill-current" />
                          Popular
                        </>
                      )}
                      {pkg.featured_type === 'special_offer' && (
                        <>
                          <Gift className="w-3 h-3 mr-1" />
                          Special Offer
                        </>
                      )}
                      {pkg.featured_type === 'seasonal' && (
                        <>
                          <Sparkles className="w-3 h-3 mr-1" />
                          Seasonal
                        </>
                      )}
                    </Badge>
                  )}
                </div>
              </CardHeader>
              <CardContent>
                <p className="text-sm text-muted-foreground line-clamp-2 mb-4">
                  {pkg.description_en}
                </p>
                <div className="flex items-center justify-between">
                  <span className="text-2xl font-bold text-primary">
                    ${pkg.price}
                  </span>
                  {pkg.duration && (
                    <span className="text-sm text-muted-foreground">
                      {pkg.duration}
                    </span>
                  )}
                </div>
              </CardContent>
              <CardFooter className="flex gap-2 bg-muted/30 border-t">
                <Button 
                  variant="outline" 
                  size="sm" 
                  className="flex-1"
                  onClick={() => handleOpenDialog(pkg)}
                >
                  <Edit className="w-4 h-4 mr-1" />
                  Edit
                </Button>
                <Button 
                  variant="destructive" 
                  size="sm"
                  onClick={() => pkg.id && handleDelete(pkg.id)}
                  disabled={deleteMutation.isPending}
                >
                  {deleteMutation.isPending ? (
                    <Loader2 className="w-4 h-4 animate-spin" />
                  ) : (
                    <Trash2 className="w-4 h-4" />
                  )}
                </Button>
              </CardFooter>
            </Card>
          ))}
        </div>
      )}
    </div>
  );
}