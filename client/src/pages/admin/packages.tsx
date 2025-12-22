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
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage } from "@/components/ui/form";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { useToast } from "@/hooks/use-toast";
import { Plus, Edit, Trash2, Loader2, Package, Eye } from "lucide-react";
import { Badge } from "@/components/ui/badge";
import { Alert, AlertDescription } from "@/components/ui/alert";

const packageSchema = z.object({
  category: z.enum(['tour', 'visa', 'ticket', 'offer']),
  title_en: z.string().min(5, "Title required"),
  description_en: z.string().min(10, "Description required"),
  price: z.string().min(1, "Price required"),
  duration: z.string().optional(),
  status: z.enum(['active', 'inactive']),
});

type PackageFormData = z.infer<typeof packageSchema>;

export default function AdminPackages() {
  const { toast } = useToast();
  const queryClient = useQueryClient();
  const [dialogOpen, setDialogOpen] = useState(false);
  const [editingPackage, setEditingPackage] = useState<AdminPackage | null>(null);
  const [selectedImage, setSelectedImage] = useState<File | null>(null);
  const [imagePreview, setImagePreview] = useState<string | null>(null);

  const { data: packages = [], isLoading, error } = useQuery({
    queryKey: ['admin-packages'],
    queryFn: async () => {
      const result = await getAdminPackages();
      // Handle different response formats
      if (Array.isArray(result)) return result;
      if (result?.data && Array.isArray(result.data)) return result.data;
      if (result?.packages && Array.isArray(result.packages)) return result.packages;
      return [];
    },
  });

  const form = useForm<PackageFormData>({
    resolver: zodResolver(packageSchema),
    defaultValues: {
      category: 'tour',
      status: 'active',
      title_en: '',
      description_en: '',
      price: '',
      duration: '',
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

  function handleOpenDialog(pkg?: AdminPackage) {
    if (pkg) {
      setEditingPackage(pkg);
      form.reset({
        category: pkg.category,
        title_en: pkg.title_en,
        description_en: pkg.description_en,
        price: pkg.price.toString(),
        duration: pkg.duration || '',
        status: pkg.status,
      });
      if (pkg.image) {
        setImagePreview(pkg.image);
      }
    } else {
      setEditingPackage(null);
      form.reset();
      setImagePreview(null);
    }
    setDialogOpen(true);
  }

  function handleCloseDialog() {
    setDialogOpen(false);
    setEditingPackage(null);
    setSelectedImage(null);
    setImagePreview(null);
    form.reset();
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

  function onSubmit(values: PackageFormData) {
    const formData = new FormData();
    Object.entries(values).forEach(([key, value]) => {
      if (value) formData.append(key, value.toString());
    });
    
    if (selectedImage) {
      formData.append('image', selectedImage);
    }
    
    if (editingPackage?.id) {
      updateMutation.mutate({ 
        id: editingPackage.id, 
        data: {
          ...values,
          price: parseFloat(values.price),
        }
      });
    } else {
      createMutation.mutate(formData);
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
            <Form {...form}>
              <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
                <div className="grid grid-cols-2 gap-4">
                  <FormField
                    control={form.control}
                    name="category"
                    render={({ field }) => (
                      <FormItem>
                        <FormLabel>Category</FormLabel>
                        <Select onValueChange={field.onChange} value={field.value}>
                          <FormControl>
                            <SelectTrigger>
                              <SelectValue />
                            </SelectTrigger>
                          </FormControl>
                          <SelectContent>
                            <SelectItem value="tour">Tour Package</SelectItem>
                            <SelectItem value="visa">Visa Service</SelectItem>
                            <SelectItem value="ticket">Flight Ticket</SelectItem>
                            <SelectItem value="offer">Special Offer</SelectItem>
                          </SelectContent>
                        </Select>
                        <FormMessage />
                      </FormItem>
                    )}
                  />

                  <FormField
                    control={form.control}
                    name="status"
                    render={({ field }) => (
                      <FormItem>
                        <FormLabel>Status</FormLabel>
                        <Select onValueChange={field.onChange} value={field.value}>
                          <FormControl>
                            <SelectTrigger>
                              <SelectValue />
                            </SelectTrigger>
                          </FormControl>
                          <SelectContent>
                            <SelectItem value="active">Active</SelectItem>
                            <SelectItem value="inactive">Inactive</SelectItem>
                          </SelectContent>
                        </Select>
                        <FormMessage />
                      </FormItem>
                    )}
                  />
                </div>

                <FormField
                  control={form.control}
                  name="title_en"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel>Title (English)</FormLabel>
                      <FormControl>
                        <Input placeholder="e.g., Dubai Shopping Festival" {...field} />
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />

                <FormField
                  control={form.control}
                  name="description_en"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel>Description (English)</FormLabel>
                      <FormControl>
                        <Textarea 
                          placeholder="Describe the package..." 
                          {...field} 
                          rows={4} 
                        />
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />

                <div className="grid grid-cols-2 gap-4">
                  <FormField
                    control={form.control}
                    name="price"
                    render={({ field }) => (
                      <FormItem>
                        <FormLabel>Price (USD)</FormLabel>
                        <FormControl>
                          <Input 
                            type="number" 
                            step="0.01" 
                            placeholder="500.00" 
                            {...field} 
                          />
                        </FormControl>
                        <FormMessage />
                      </FormItem>
                    )}
                  />

                  <FormField
                    control={form.control}
                    name="duration"
                    render={({ field }) => (
                      <FormItem>
                        <FormLabel>Duration (Optional)</FormLabel>
                        <FormControl>
                          <Input placeholder="e.g., 5 Days" {...field} />
                        </FormControl>
                        <FormMessage />
                      </FormItem>
                    )}
                  />
                </div>

                <div>
                  <FormLabel>Package Image</FormLabel>
                  <Input 
                    type="file" 
                    accept="image/*"
                    onChange={handleImageChange}
                    className="mt-2"
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
            </Form>
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
          </CardContent>
        </Card>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
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
                <Badge variant="outline" className="w-fit text-xs mt-2">
                  {pkg.category}
                </Badge>
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